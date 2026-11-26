<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\ConfiguracionModelo;
use App\Models\HistorialIncidente;
use App\Models\Incidente;
use App\Models\Tactica;
use App\Models\TipoIncidente;
use App\Models\Usuario;
use App\Servicios\Bitacora;
use App\Servicios\EvaluadorIncidente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncidenteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incidentes.ver,admin')->only(['listar', 'mostrar']);
        $this->middleware('permission:incidentes.crear,admin')->only(['guardar']);
        $this->middleware('permission:incidentes.editar,admin')->only(['actualizar']);
        $this->middleware('permission:incidentes.asignar,admin')->only(['seguimiento']);
        $this->middleware('permission:incidentes.eliminar,admin')->only(['eliminar']);
    }

    private const RELACIONES = ['tipo', 'usuarioAsignado', 'activo', 'tacticas'];

    public function listar(Request $request)
    {
        $incidentes = Incidente::with(self::RELACIONES)->orderByDesc('creado_en')->orderByDesc('id')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($incidentes);
        }

        return view('panel.incidentes.listado', array_merge(['incidentes' => $incidentes], $this->catalogos()));
    }

    public function mostrar($id)
    {
        $incidente = Incidente::with(array_merge(self::RELACIONES, [
            'comentarios.autor', 'historial.responsable', 'evidencias.autor',
            'evaluaciones.analista', 'evaluaciones.configuracion:id,version', 'ultimaEvaluacion.valoraciones',
        ]))->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($incidente);
        }

        $estructura = EvaluadorIncidente::estructura();
        $configuracion = ConfiguracionModelo::vigente();

        return view('panel.incidentes.detalle', array_merge(
            compact('incidente', 'estructura', 'configuracion'),
            $this->catalogos()
        ));
    }

    public function guardar(Request $request)
    {
        $datos = $this->validate($request, $this->reglas(false));
        $usuario = auth('admin')->user();

        // Solo el responsable de seguridad asigna y cambia estados (casos de uso 3.3.2.3).
        if (! $usuario->can('incidentes.asignar')) {
            $datos['estado'] = 'abierto';
            $datos['asignado_a'] = null;
        }
        $datos['estado'] = $datos['estado'] ?? 'abierto';
        $datos['detectado_en'] = $datos['detectado_en'] ?? now();
        $tacticas = $this->tacticasSegunOrigen($datos);

        $incidente = DB::transaction(function () use ($datos, $tacticas) {
            $incidente = Incidente::create($datos);
            $incidente->tacticas()->sync($tacticas);

            HistorialIncidente::create([
                'incidente_id' => $incidente->id,
                'estado_anterior' => null,
                'estado_nuevo' => $incidente->estado,
                'cambiado_por' => auth('admin')->id(),
                'notas' => 'Incidente registrado',
            ]);

            return $incidente;
        });

        Bitacora::registrar('incidente.registrar', 'incidente', $incidente->id, ['codigo' => $incidente->codigo]);

        return response()->json($incidente->load(self::RELACIONES), 201);
    }

    public function actualizar(Request $request, $id)
    {
        $incidente = Incidente::with('tacticas')->findOrFail($id);
        $datos = $this->validate($request, $this->reglas(true));
        $usuario = auth('admin')->user();

        $cambiaSeguimiento = $datos['estado'] !== $incidente->estado
            || (int) ($datos['asignado_a'] ?? 0) !== (int) ($incidente->asignado_a ?? 0);
        if ($cambiaSeguimiento && ! $usuario->can('incidentes.asignar')) {
            return response()->json(['message' => 'Solo el responsable de seguridad puede asignar o cambiar el estado.'], 403);
        }

        $tacticas = $this->tacticasSegunOrigen($datos);
        $antes = [
            'calificacion' => $incidente->calificacion,
            'origen' => $incidente->origen,
            'activo_id' => $incidente->activo_id,
            'tacticas' => $incidente->tacticas->pluck('id')->sort()->values()->all(),
        ];

        DB::transaction(function () use ($incidente, $datos, $tacticas, $request) {
            $estadoAnterior = $incidente->estado;
            $asignadoAnterior = $incidente->asignado_a;
            $incidente->update($datos);
            $incidente->tacticas()->sync($tacticas);
            $this->registrarSeguimiento($incidente, $estadoAnterior, $asignadoAnterior, $request->input('notas_estado'));
        });

        $incidente->load('tacticas');
        $despues = [
            'calificacion' => $incidente->calificacion,
            'origen' => $incidente->origen,
            'activo_id' => $incidente->activo_id,
            'tacticas' => $incidente->tacticas->pluck('id')->sort()->values()->all(),
        ];

        // Si cambió la clasificación, el resultado anterior deja de ser válido: se recalcula.
        if ($antes != $despues && $incidente->evaluado_en) {
            (new EvaluadorIncidente)->reevaluar($incidente->fresh(), 'Reclasificación del incidente', auth('admin')->id());
        }

        Bitacora::registrar('incidente.editar', 'incidente', $incidente->id, ['codigo' => $incidente->codigo]);

        return response()->json($incidente->fresh()->load(self::RELACIONES));
    }

    /**
     * Asignación al responsable y cambio de estado hasta el cierre (RF8).
     */
    public function seguimiento(Request $request, $id)
    {
        $incidente = Incidente::findOrFail($id);
        $datos = $this->validate($request, [
            'estado' => 'required|in:' . implode(',', Incidente::ESTADOS),
            'asignado_a' => 'nullable|exists:usuarios,id',
            'notas' => 'nullable|max:1000',
        ]);

        DB::transaction(function () use ($incidente, $datos) {
            $estadoAnterior = $incidente->estado;
            $asignadoAnterior = $incidente->asignado_a;
            $incidente->update(['estado' => $datos['estado'], 'asignado_a' => $datos['asignado_a'] ?? null]);
            $this->registrarSeguimiento($incidente, $estadoAnterior, $asignadoAnterior, $datos['notas'] ?? null);
        });

        Bitacora::registrar('incidente.seguimiento', 'incidente', $incidente->id, [
            'estado' => $incidente->estado,
            'asignado_a' => $incidente->asignado_a,
        ]);

        return response()->json($incidente->fresh()->load(['usuarioAsignado', 'historial.responsable']));
    }

    public function eliminar($id)
    {
        $incidente = Incidente::findOrFail($id);
        Bitacora::registrar('incidente.eliminar', 'incidente', $incidente->id, ['codigo' => $incidente->codigo, 'titulo' => $incidente->titulo]);
        $incidente->delete();

        return response()->json('ok');
    }

    /**
     * Cada cambio de estado o de responsable queda en el historial con usuario y fecha (RNF2).
     */
    private function registrarSeguimiento(Incidente $incidente, $estadoAnterior, $asignadoAnterior, $notas): void
    {
        $cambioEstado = $estadoAnterior !== $incidente->estado;
        $cambioAsignado = (int) ($asignadoAnterior ?? 0) !== (int) ($incidente->asignado_a ?? 0);
        if (! $cambioEstado && ! $cambioAsignado) return;

        $partes = [];
        if ($cambioAsignado) {
            $partes[] = 'Asignado a: ' . (optional(Usuario::find($incidente->asignado_a))->nombre ?? 'sin asignar');
        }
        if ($notas) $partes[] = $notas;

        HistorialIncidente::create([
            'incidente_id' => $incidente->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $incidente->estado,
            'cambiado_por' => auth('admin')->id(),
            'notas' => implode(' · ', $partes) ?: null,
        ]);
    }

    /**
     * La táctica solo se registra en incidentes de origen adversario (clasificación de doble eje).
     */
    private function tacticasSegunOrigen(array &$datos): array
    {
        $tacticas = $datos['tacticas'] ?? [];
        unset($datos['tacticas']);

        return ($datos['origen'] ?? null) === 'adversario' ? $tacticas : [];
    }

    private function catalogos(): array
    {
        return [
            'tipos' => TipoIncidente::where('activo', true)->orderBy('nombre')->get(),
            'usuarios' => Usuario::orderBy('nombre')->get(['id', 'nombre', 'usuario']),
            'tacticas' => Tactica::where('activo', true)->orderBy('orden')->get(),
            'activos' => Activo::where('activo', true)->orderBy('codigo')->get(['id', 'codigo', 'nombre', 'criticidad']),
        ];
    }

    private function reglas(bool $actualizando): array
    {
        return [
            'titulo' => 'required|max:255',
            'descripcion' => 'required',
            'tipo_incidente_id' => 'required|exists:tipos_incidente,id',
            'calificacion' => 'required|in:' . implode(',', Incidente::CALIFICACIONES),
            'origen' => 'required|in:' . implode(',', Incidente::ORIGENES),
            'tacticas' => 'nullable|array',
            'tacticas.*' => 'integer|exists:tacticas,id',
            'activo_id' => 'nullable|exists:activos,id',
            'estado' => ($actualizando ? 'required' : 'nullable') . '|in:' . implode(',', Incidente::ESTADOS),
            'reportado_por' => 'nullable|max:255',
            'asignado_a' => 'nullable|exists:usuarios,id',
            'detectado_en' => 'nullable|date',
            'activos_afectados' => 'nullable',
            'impacto' => 'nullable',
            'remediacion' => 'nullable',
        ];
    }
}
