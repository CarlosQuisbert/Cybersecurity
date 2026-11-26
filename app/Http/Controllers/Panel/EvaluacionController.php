<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Incidente;
use App\Servicios\Bitacora;
use App\Servicios\EvaluadorIncidente;
use Illuminate\Http\Request;

/**
 * Evaluación multicriterio y priorización automática de un incidente (RF4, RF5, RF6).
 */
class EvaluacionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:incidentes.evaluar,admin']);
    }

    public function guardar(Request $request, EvaluadorIncidente $evaluador, $incidenteId)
    {
        $this->validate($request, [
            'valoraciones' => 'required|array',
            'valoraciones.*' => 'integer|between:1,5',
        ]);

        $incidente = Incidente::findOrFail($incidenteId);
        $evaluacion = $evaluador->evaluar($incidente, $request->input('valoraciones'), auth('admin')->id(), 'Evaluación del analista');

        Bitacora::registrar('incidente.evaluar', 'incidente', $incidente->id, [
            'evaluacion_id' => $evaluacion->id,
            'indice_riesgo' => $evaluacion->indice_riesgo,
            'nivel_prioridad' => $evaluacion->nivel_prioridad,
            'configuracion' => $evaluacion->detalle['configuracion_version'],
        ]);

        return response()->json([
            'evaluacion' => $evaluacion->load(['analista', 'configuracion:id,version', 'valoraciones']),
            'incidente' => $incidente->fresh()->only(['id', 'indice_riesgo', 'nivel_prioridad', 'progresion_nivel', 'evaluado_en']),
        ], 201);
    }
}
