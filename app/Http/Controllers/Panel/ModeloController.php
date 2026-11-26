<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BloqueCriterio;
use App\Models\ConfiguracionModelo;
use App\Models\Escala;
use App\Models\Incidente;
use App\Servicios\AnalisisSensibilidad;
use App\Servicios\Bitacora;
use App\Servicios\EvaluadorIncidente;
use App\Servicios\MotorRiesgo;
use App\Servicios\ProcesoAnaliticoJerarquico as AHP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Configuración del modelo sin tocar el código (RF10, RNF3): pesos, umbrales,
 * escalas, cálculo de pesos por AHP y análisis de sensibilidad.
 */
class ModeloController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:modelo.configurar,admin']);
    }

    // ------------------------------------------------------------------
    // Configuración versionada
    // ------------------------------------------------------------------

    public function configuracion()
    {
        $vigente = ConfiguracionModelo::vigente();
        $versiones = ConfiguracionModelo::with('autor:id,nombre')->orderByDesc('version')->get();
        $bloques = BloqueCriterio::with('criterios')->orderBy('orden')->get();
        $pesosBloque = $vigente ? $vigente->pesosBloque->pluck('peso', 'bloque_id') : collect();
        $pesosCriterio = $vigente ? $vigente->pesosCriterio->pluck('peso_local', 'criterio_id') : collect();
        $pesosGlobales = $vigente ? $vigente->pesosGlobales() : [];
        $evaluados = Incidente::whereNotNull('evaluado_en')->count();
        $desactualizados = $vigente ? Incidente::whereNotNull('evaluado_en')
            ->whereHas('ultimaEvaluacion', fn ($q) => $q->where('configuracion_id', '!=', $vigente->id))->count() : 0;

        return view('panel.modelo.configuracion', compact(
            'vigente', 'versiones', 'bloques', 'pesosBloque', 'pesosCriterio', 'pesosGlobales', 'evaluados', 'desactualizados'
        ));
    }

    /**
     * Publica una nueva versión de la configuración; la anterior se conserva.
     */
    public function guardarConfiguracion(Request $request)
    {
        $datos = $this->validate($request, [
            'descripcion' => 'nullable|max:2000',
            'version_attack' => 'required|max:40',
            'umbral_critico' => 'required|numeric|between:0,100',
            'umbral_alto' => 'required|numeric|between:0,100',
            'umbral_medio' => 'required|numeric|between:0,100',
            'capacidad_criticos' => 'required|integer|min:1|max:100',
            'pesos_bloque' => 'required|array',
            'pesos_bloque.*' => 'required|numeric|min:0|max:1',
            'pesos_criterio' => 'required|array',
            'pesos_criterio.*' => 'required|numeric|min:0|max:1',
            'origen_pesos' => 'nullable|array',
            'recalcular' => 'nullable|boolean',
        ]);

        try {
            MotorRiesgo::validarUmbrales([
                'critico' => (float) $datos['umbral_critico'],
                'alto' => (float) $datos['umbral_alto'],
                'medio' => (float) $datos['umbral_medio'],
            ]);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['umbral_critico' => $e->getMessage()]);
        }

        $this->validarPesos($datos['pesos_bloque'], $datos['pesos_criterio']);

        $configuracion = DB::transaction(function () use ($datos) {
            ConfiguracionModelo::where('vigente', true)->update(['vigente' => false]);

            $configuracion = ConfiguracionModelo::create([
                'version' => (int) ConfiguracionModelo::max('version') + 1,
                'descripcion' => $datos['descripcion'] ?? null,
                'version_attack' => $datos['version_attack'],
                'umbral_critico' => $datos['umbral_critico'],
                'umbral_alto' => $datos['umbral_alto'],
                'umbral_medio' => $datos['umbral_medio'],
                'capacidad_criticos' => $datos['capacidad_criticos'],
                'origen_pesos' => $datos['origen_pesos'] ?? ['metodo' => 'Ingreso manual'],
                'vigente' => true,
                'creado_por' => auth('admin')->id(),
            ]);

            foreach ($datos['pesos_bloque'] as $bloqueId => $peso) {
                $configuracion->pesosBloque()->create(['bloque_id' => $bloqueId, 'peso' => $peso]);
            }
            foreach ($datos['pesos_criterio'] as $criterioId => $peso) {
                $configuracion->pesosCriterio()->create(['criterio_id' => $criterioId, 'peso_local' => $peso]);
            }

            return $configuracion;
        });

        Bitacora::registrar('modelo.nueva_version', 'configuracion', $configuracion->id, [
            'version' => $configuracion->version,
            'pesos_globales' => array_map(fn ($p) => round($p, 4), $configuracion->pesosGlobales()),
            'umbrales' => $configuracion->umbrales(),
        ]);

        $recalculados = $request->boolean('recalcular') ? $this->recalcularTodos() : 0;
        $mensaje = "Se publicó la versión {$configuracion->version} de la configuración."
            . ($recalculados ? " Se recalcularon {$recalculados} incidentes." : '');

        if ($request->wantsJson()) {
            return response()->json(['message' => $mensaje, 'configuracion' => $configuracion], 201);
        }

        return redirect()->route('modelo.configuracion')->with('mensaje', $mensaje);
    }

    /**
     * Vuelve a poner vigente una versión anterior.
     */
    public function activar($id)
    {
        $configuracion = ConfiguracionModelo::findOrFail($id);
        DB::transaction(function () use ($configuracion) {
            ConfiguracionModelo::where('vigente', true)->update(['vigente' => false]);
            $configuracion->update(['vigente' => true]);
        });
        Bitacora::registrar('modelo.activar_version', 'configuracion', $configuracion->id, ['version' => $configuracion->version]);

        return redirect()->route('modelo.configuracion')->with('mensaje', "La versión {$configuracion->version} quedó vigente.");
    }

    /**
     * Recalcula todos los incidentes evaluados con la configuración vigente,
     * conservando los niveles elegidos por el analista y las evaluaciones anteriores.
     */
    public function recalcular()
    {
        $total = $this->recalcularTodos();

        return redirect()->route('modelo.configuracion')->with('mensaje', "Se recalcularon {$total} incidentes con la configuración vigente.");
    }

    private function recalcularTodos(): int
    {
        $evaluador = new EvaluadorIncidente;
        $total = 0;
        $vigente = ConfiguracionModelo::vigente();
        Incidente::whereNotNull('evaluado_en')->get()->each(function ($incidente) use ($evaluador, &$total, $vigente) {
            if ($evaluador->reevaluar($incidente, 'Recálculo con la configuración versión ' . $vigente->version, auth('admin')->id())) {
                $total++;
            }
        });
        Bitacora::registrar('modelo.recalcular', 'configuracion', $vigente->id, ['incidentes' => $total]);

        return $total;
    }

    private function validarPesos(array $pesosBloque, array $pesosCriterio): void
    {
        $bloques = BloqueCriterio::with('criterios')->get();
        $errores = [];

        if ($bloques->pluck('id')->diff(array_keys($pesosBloque))->isNotEmpty()) {
            $errores['pesos_bloque'] = 'Falta el peso de algún bloque.';
        } elseif (abs(array_sum($pesosBloque) - 1) > 0.01) {
            $errores['pesos_bloque'] = 'Los pesos de los bloques deben sumar 1 (suman ' . round(array_sum($pesosBloque), 4) . ').';
        }

        foreach ($bloques as $bloque) {
            $locales = array_intersect_key($pesosCriterio, array_flip($bloque->criterios->pluck('id')->all()));
            if (count($locales) !== $bloque->criterios->count()) {
                $errores["pesos_criterio.{$bloque->id}"] = "Falta el peso de algún criterio del bloque «{$bloque->nombre}».";
            } elseif (abs(array_sum($locales) - 1) > 0.01) {
                $errores["pesos_criterio.{$bloque->id}"] = "Los pesos locales del bloque «{$bloque->nombre}» deben sumar 1 (suman " . round(array_sum($locales), 4) . ').';
            }
        }

        if ($errores) throw ValidationException::withMessages($errores);
    }

    // ------------------------------------------------------------------
    // Escalas de valoración
    // ------------------------------------------------------------------

    public function escalas()
    {
        $criterios = EvaluadorIncidente::estructura();

        return view('panel.modelo.escalas', compact('criterios'));
    }

    public function actualizarEscala(Request $request, $id)
    {
        $escala = Escala::with('niveles')->findOrFail($id);
        $datos = $this->validate($request, [
            'niveles' => 'required|array|size:5',
            'niveles.*' => 'required|string|max:1000',
            'nota' => 'nullable|max:2000',
        ]);

        $anteriores = $escala->niveles->pluck('descriptor', 'nivel')->all();
        DB::transaction(function () use ($escala, $datos) {
            $escala->update(['nota' => $datos['nota'] ?? null]);
            foreach ($datos['niveles'] as $nivel => $descriptor) {
                $escala->niveles()->updateOrCreate(['nivel' => (int) $nivel], ['descriptor' => $descriptor]);
            }
        });

        Bitacora::registrar('modelo.editar_escala', 'escala', $escala->id, [
            'escala' => $escala->nombre,
            'anteriores' => $anteriores,
        ]);

        return redirect()->route('modelo.escalas')->with('mensaje', "Se guardaron los descriptores de «{$escala->nombre}».");
    }

    // ------------------------------------------------------------------
    // Proceso analítico jerárquico
    // ------------------------------------------------------------------

    public function ahp()
    {
        $vigente = ConfiguracionModelo::vigente();

        return view('panel.modelo.ahp', [
            'estructura' => $this->estructuraAhp(),
            'vigente' => $vigente,
        ]);
    }

    /**
     * Calcula los pesos a partir de los juicios de uno o varios expertos:
     * CR de cada matriz individual, agregación por media geométrica y CR agregada.
     */
    public function calcularAhp(Request $request)
    {
        $this->validate($request, [
            'expertos' => 'required|array|min:1',
            'expertos.*.nombre' => 'nullable|max:60',
            'expertos.*.juicios' => 'required|array',
        ]);

        $expertos = $request->input('expertos');
        $resultado = [];
        $todoConsistente = true;

        foreach ($this->estructuraAhp() as $matriz) {
            $n = count($matriz['elementos']);
            $individuales = [];
            $matrices = [];

            foreach ($expertos as $e => $experto) {
                $juicios = [];
                foreach ($matriz['pares'] as $par) {
                    $valor = $experto['juicios'][$par['clave']] ?? null;
                    if ($valor === null || $valor === '') {
                        throw ValidationException::withMessages([
                            "expertos.{$e}.juicios.{$par['clave']}" => 'Falta el juicio «' . $par['texto'] . '» de ' . ($experto['nombre'] ?: 'Experto ' . ($e + 1)) . '.',
                        ]);
                    }
                    $juicios[] = $valor;
                }

                try {
                    $m = AHP::matrizDesdeJuicios($n, $juicios);
                } catch (InvalidArgumentException $ex) {
                    throw ValidationException::withMessages(["expertos.{$e}" => $ex->getMessage()]);
                }

                $analisis = AHP::analizar($m);
                $inconsistentes = $analisis['consistente'] ? [] : array_map(
                    fn ($c) => $matriz['pares'][$this->indicePar($matriz['pares'], $c['i'], $c['j'])]['texto'],
                    AHP::comparacionesMasInconsistentes($m, $analisis['pesos'])
                );
                $todoConsistente = $todoConsistente && $analisis['consistente'];

                $matrices[] = $m;
                $individuales[] = [
                    'experto' => $experto['nombre'] ?: 'Experto ' . ($e + 1),
                    'pesos' => $analisis['pesos'],
                    'cr' => $analisis['cr'],
                    'consistente' => $analisis['consistente'],
                    'revisar' => $inconsistentes,
                ];
            }

            $agregada = AHP::agregarGeometrica($matrices);
            $analisis = AHP::analizar($agregada);
            $todoConsistente = $todoConsistente && $analisis['consistente'];

            $resultado[] = [
                'clave' => $matriz['clave'],
                'titulo' => $matriz['titulo'],
                'elementos' => $matriz['elementos'],
                'individuales' => $individuales,
                'agregada' => $agregada,
                'pesos' => $analisis['pesos'],
                'lambda_max' => $analisis['lambda_max'],
                'ci' => $analisis['ci'],
                'ri' => $analisis['ri'],
                'cr' => $analisis['cr'],
                'consistente' => $analisis['consistente'],
            ];
        }

        // Pesos listos para guardar como nueva configuración.
        $pesosBloque = [];
        $pesosCriterio = [];
        foreach ($resultado as $r) {
            foreach ($r['elementos'] as $k => $elemento) {
                if ($r['clave'] === 'bloques') {
                    $pesosBloque[$elemento['id']] = round($r['pesos'][$k], 6);
                } else {
                    $pesosCriterio[$elemento['id']] = round($r['pesos'][$k], 6);
                }
            }
        }

        return response()->json([
            'matrices' => $resultado,
            'consistente' => $todoConsistente,
            'pesos_bloque' => $pesosBloque,
            'pesos_criterio' => $pesosCriterio,
            'expertos' => count($expertos),
        ]);
    }

    /**
     * Matrices de comparación: los bloques entre sí y los criterios de cada bloque
     * (12 juicios por experto con la jerarquía de la tesis).
     */
    private function estructuraAhp(): array
    {
        $bloques = BloqueCriterio::with('criterios')->orderBy('orden')->get();
        $matrices = [$this->matrizAhp('bloques', 'Los bloques entre sí', $bloques)];
        foreach ($bloques as $bloque) {
            $matrices[] = $this->matrizAhp('bloque_' . $bloque->id, 'Criterios del bloque «' . $bloque->nombre . '»', $bloque->criterios);
        }

        return array_values(array_filter($matrices, fn ($m) => count($m['elementos']) > 1));
    }

    private function matrizAhp(string $clave, string $titulo, $elementos): array
    {
        $lista = $elementos->values()->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])->all();
        $pares = [];
        for ($i = 0; $i < count($lista); $i++) {
            for ($j = $i + 1; $j < count($lista); $j++) {
                $pares[] = [
                    'clave' => "{$clave}:{$i}:{$j}",
                    'i' => $i,
                    'j' => $j,
                    'texto' => $lista[$i]['nombre'] . ' frente a ' . $lista[$j]['nombre'],
                ];
            }
        }

        return ['clave' => $clave, 'titulo' => $titulo, 'elementos' => $lista, 'pares' => $pares];
    }

    private function indicePar(array $pares, int $i, int $j): int
    {
        foreach ($pares as $k => $par) {
            if ($par['i'] === $i && $par['j'] === $j) return $k;
        }

        return 0;
    }

    // ------------------------------------------------------------------
    // Análisis de sensibilidad
    // ------------------------------------------------------------------

    public function sensibilidad()
    {
        $vigente = ConfiguracionModelo::vigente();
        $bloques = BloqueCriterio::with('criterios')->orderBy('orden')->get();

        $pesosBloque = [];
        $pesosLocales = [];
        $porBloque = $vigente->pesosBloque->pluck('peso', 'bloque_id');
        $porCriterio = $vigente->pesosCriterio->pluck('peso_local', 'criterio_id');
        foreach ($bloques as $bloque) {
            $pesosBloque[$bloque->codigo] = (float) $porBloque[$bloque->id];
            foreach ($bloque->criterios as $criterio) {
                $pesosLocales[$bloque->codigo][$criterio->codigo] = (float) $porCriterio[$criterio->id];
            }
        }

        $casos = [];
        $omitidos = 0;
        $incidentes = Incidente::with('ultimaEvaluacion')->whereNotNull('evaluado_en')->get();
        foreach ($incidentes as $incidente) {
            $detalle = optional($incidente->ultimaEvaluacion)->detalle;
            if (! $detalle) { $omitidos++; continue; }
            $niveles = collect($detalle['criterios'])->filter(fn ($c) => isset($c['nivel']))->map(fn ($c) => $c['nivel'])->all();
            $casos[$incidente->codigo] = ['niveles' => $niveles, 'adversario' => $incidente->ultimaEvaluacion->origen === 'adversario'];
        }

        $analisis = null;
        if (count($casos) >= 2) {
            try {
                $analisis = AnalisisSensibilidad::ejecutar($casos, $pesosBloque, $pesosLocales, $vigente->umbrales());
            } catch (InvalidArgumentException $e) {
                $analisis = null;
            }
        }

        $nombresBloque = $bloques->pluck('nombre', 'codigo');

        return view('panel.modelo.sensibilidad', compact('vigente', 'analisis', 'casos', 'omitidos', 'nombresBloque'));
    }
}
