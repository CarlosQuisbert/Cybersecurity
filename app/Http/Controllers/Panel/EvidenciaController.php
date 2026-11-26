<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EvidenciaIncidente;
use App\Models\Incidente;
use App\Servicios\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Evidencias con control de acceso y trazabilidad (RF9, control 5.28 de la ISO 27002).
 * No se eliminan: su historial se conserva y cada descarga queda en la bitácora.
 */
class EvidenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incidentes.ver,admin')->only(['descargar']);
        $this->middleware('permission:evidencias.adjuntar,admin')->only(['guardar']);
    }

    public function guardar(Request $request, $incidenteId)
    {
        $incidente = Incidente::findOrFail($incidenteId);
        $this->validate($request, [
            'archivo' => 'required|file|max:20480',
            'descripcion' => 'nullable|max:1000',
        ]);

        $archivo = $request->file('archivo');
        $hash = hash_file('sha256', $archivo->getRealPath());
        $ruta = $archivo->storeAs(
            'evidencias/' . $incidente->id,
            Str::uuid() . ($archivo->getClientOriginalExtension() ? '.' . $archivo->getClientOriginalExtension() : ''),
            'local'
        );

        $evidencia = EvidenciaIncidente::create([
            'incidente_id' => $incidente->id,
            'usuario_id' => auth('admin')->id(),
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'tipo_mime' => $archivo->getClientMimeType(),
            'tamano' => $archivo->getSize(),
            'hash_sha256' => $hash,
            'descripcion' => $request->input('descripcion'),
        ]);

        Bitacora::registrar('evidencia.adjuntar', 'incidente', $incidente->id, [
            'evidencia_id' => $evidencia->id,
            'archivo' => $evidencia->nombre_original,
            'sha256' => $hash,
        ]);

        return response()->json($evidencia->load('autor'), 201);
    }

    public function descargar($incidenteId, $id)
    {
        $evidencia = EvidenciaIncidente::where('incidente_id', $incidenteId)->findOrFail($id);

        abort_unless(Storage::disk('local')->exists($evidencia->ruta), 404, 'El archivo de la evidencia no existe.');

        Bitacora::registrar('evidencia.descargar', 'incidente', $evidencia->incidente_id, [
            'evidencia_id' => $evidencia->id,
            'archivo' => $evidencia->nombre_original,
        ]);

        return Storage::disk('local')->download($evidencia->ruta, $evidencia->nombre_original);
    }
}
