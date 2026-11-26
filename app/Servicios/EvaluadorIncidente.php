<?php

namespace App\Servicios;

use App\Models\ConfiguracionModelo;
use App\Models\Criterio;
use App\Models\Evaluacion;
use App\Models\Incidente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Aplica el modelo de priorización a un incidente:
 *  - toma los niveles que el analista eligió en cada escala,
 *  - obtiene la criticidad del activo desde el inventario (o nivel 1 y registra la omisión),
 *  - obtiene la progresión de la táctica más avanzada (o la excluye si no es adversario),
 *  - calcula con el motor usando la configuración vigente y guarda el resultado.
 */
class EvaluadorIncidente
{
    /**
     * Criterios con su bloque, escalas y descriptores, en el orden de la tesis.
     */
    public static function estructura()
    {
        return Criterio::with(['bloque', 'escalas.niveles'])
            ->join('bloques_criterio', 'bloques_criterio.id', '=', 'criterios.bloque_id')
            ->orderBy('bloques_criterio.orden')->orderBy('criterios.orden')
            ->select('criterios.*')
            ->get();
    }

    /**
     * @param array       $valores [escala_id => nivel] elegidos por el analista
     * @param string|null $motivo  p. ej. "Reclasificación" o "Nueva configuración"
     */
    public function evaluar(Incidente $incidente, array $valores, $usuarioId = null, ?string $motivo = null, ?ConfiguracionModelo $configuracion = null): Evaluacion
    {
        $incidente->loadMissing(['activo', 'tacticas']);

        $this->verificarClasificacion($incidente);

        $configuracion = $configuracion ?: ConfiguracionModelo::vigente();
        if (! $configuracion) {
            throw ValidationException::withMessages(['configuracion' => 'No hay una configuración vigente del modelo.']);
        }

        $adversario = $incidente->esAdversario();
        $pesos = $configuracion->pesosGlobales();
        $criterios = self::estructura();

        $niveles = [];
        $valoraciones = [];
        $detalleCriterios = [];
        $omisionInventario = false;
        $errores = [];

        foreach ($criterios as $criterio) {
            $nivelesEscala = [];

            if ($criterio->fuente === Criterio::FUENTE_INVENTARIO) {
                $escala = $criterio->escalas->first();
                if ($incidente->activo) {
                    $nivelesEscala[$escala->id] = (int) $incidente->activo->criticidad;
                } else {
                    // Activo no inventariado: nivel mínimo y se registra la omisión (escala d).
                    $nivelesEscala[$escala->id] = 1;
                    $omisionInventario = true;
                }
            } elseif ($criterio->fuente === Criterio::FUENTE_TACTICAS) {
                if (! $adversario) {
                    $detalleCriterios[$criterio->codigo] = $this->detalleCriterio($criterio, [], 'excluido');
                    continue;
                }
                $escala = $criterio->escalas->first();
                $nivelesEscala[$escala->id] = (int) $incidente->nivelProgresion();
            } else {
                foreach ($criterio->escalas as $escala) {
                    $nivel = $valores[$escala->id] ?? null;
                    if (! is_numeric($nivel) || (int) $nivel < 1 || (int) $nivel > 5) {
                        $errores["valoraciones.{$escala->id}"] = "Seleccione un nivel para «{$escala->nombre}».";
                        continue;
                    }
                    $nivelesEscala[$escala->id] = (int) $nivel;
                }
            }

            if (count($nivelesEscala) !== $criterio->escalas->count()) continue;

            $niveles[$criterio->codigo] = MotorRiesgo::combinar(array_values($nivelesEscala), $criterio->combinacion);
            $valoraciones += $nivelesEscala;
            $detalleCriterios[$criterio->codigo] = $this->detalleCriterio($criterio, $nivelesEscala, $criterio->fuente);
        }

        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        $soloAdversario = $criterios->where('solo_adversario', true)->pluck('codigo')->all();
        $calculo = MotorRiesgo::calcular($pesos, $niveles, $adversario, $configuracion->umbrales(), $soloAdversario);

        foreach ($calculo['aportes'] as $codigo => $aporte) {
            $detalleCriterios[$codigo] += [
                'nivel' => $aporte['nivel'],
                'valor' => $aporte['valor'],
                'peso_aplicado' => round($aporte['peso'], 6),
                'aporte' => round($aporte['aporte'], 2),
            ];
        }
        foreach ($detalleCriterios as $codigo => &$d) {
            $d['peso_configurado'] = round($pesos[$codigo] ?? 0, 6);
        }
        unset($d);

        $progresion = $adversario ? ($niveles[MotorRiesgo::CRITERIO_PROGRESION] ?? $incidente->nivelProgresion()) : null;

        return DB::transaction(function () use ($incidente, $configuracion, $usuarioId, $calculo, $adversario, $progresion, $omisionInventario, $motivo, $detalleCriterios, $valoraciones) {
            $evaluacion = Evaluacion::create([
                'incidente_id' => $incidente->id,
                'configuracion_id' => $configuracion->id,
                'usuario_id' => $usuarioId,
                'indice_riesgo' => $calculo['indice'],
                'nivel_prioridad' => $calculo['nivel_prioridad'],
                'origen' => $incidente->origen,
                'progresion_nivel' => $progresion,
                'progresion_excluida' => ! $adversario,
                'omision_inventario' => $omisionInventario,
                'motivo' => $motivo,
                'detalle' => [
                    'configuracion_version' => $configuracion->version,
                    'version_attack' => $configuracion->version_attack,
                    'umbrales' => $configuracion->umbrales(),
                    'criterios' => $detalleCriterios,
                    'activo' => $incidente->activo ? $incidente->activo->only(['id', 'codigo', 'nombre', 'criticidad']) : null,
                    'tacticas' => $incidente->tacticas->map->only(['id', 'identificador', 'nombre', 'nivel_progresion'])->values()->all(),
                ],
            ]);

            foreach ($valoraciones as $escalaId => $nivel) {
                $evaluacion->valoraciones()->create(['escala_id' => $escalaId, 'nivel' => $nivel]);
            }

            $incidente->forceFill([
                'indice_riesgo' => $calculo['indice'],
                'nivel_prioridad' => $calculo['nivel_prioridad'],
                'progresion_nivel' => $progresion,
                'evaluado_en' => now(),
            ])->saveQuietly();

            return $evaluacion;
        });
    }

    /**
     * Recalcula con los mismos niveles elegidos por el analista en la última
     * evaluación (tras reclasificar el incidente o publicar una nueva configuración).
     */
    public function reevaluar(Incidente $incidente, string $motivo, $usuarioId = null): ?Evaluacion
    {
        $ultima = $incidente->ultimaEvaluacion()->with('valoraciones')->first();
        if (! $ultima) return null;

        $incidente->unsetRelation('tacticas')->unsetRelation('activo');

        try {
            $this->verificarClasificacion($incidente);
        } catch (ValidationException $e) {
            // Ya no puede evaluarse (p. ej. se descartó): se quita de la cola.
            $incidente->forceFill(['indice_riesgo' => null, 'nivel_prioridad' => null, 'progresion_nivel' => null])->saveQuietly();
            return null;
        }

        $valores = $ultima->valoraciones->pluck('nivel', 'escala_id')->all();

        return $this->evaluar($incidente, $valores, $usuarioId, $motivo);
    }

    private function verificarClasificacion(Incidente $incidente): void
    {
        if ($incidente->calificacion !== 'incidente') {
            throw ValidationException::withMessages(['calificacion' => 'Solo se evalúan los eventos calificados como incidente.']);
        }
        if (! in_array($incidente->origen, Incidente::ORIGENES, true)) {
            throw ValidationException::withMessages(['origen' => 'Indique el origen del incidente antes de evaluarlo.']);
        }
        if ($incidente->esAdversario() && $incidente->tacticas->isEmpty()) {
            throw ValidationException::withMessages(['tacticas' => 'Un incidente de origen adversario requiere al menos una táctica ATT&CK.']);
        }
    }

    private function detalleCriterio(Criterio $criterio, array $nivelesEscala, string $fuente): array
    {
        return [
            'nombre' => $criterio->nombre,
            'bloque' => $criterio->bloque->nombre,
            'fuente' => $fuente,
            'escalas' => $criterio->escalas->filter(fn ($e) => isset($nivelesEscala[$e->id]))->map(function ($escala) use ($nivelesEscala) {
                $nivel = $nivelesEscala[$escala->id];
                return [
                    'escala_id' => $escala->id,
                    'nombre' => $escala->nombre,
                    'nivel' => $nivel,
                    'descriptor' => optional($escala->niveles->firstWhere('nivel', $nivel))->descriptor,
                ];
            })->values()->all(),
        ];
    }
}
