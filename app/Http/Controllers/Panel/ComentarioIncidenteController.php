<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ComentarioIncidente;
use App\Models\Incidente;
use Illuminate\Http\Request;

class ComentarioIncidenteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incidentes.ver,admin')->only(['listar']);
        $this->middleware('permission:incidentes.editar,admin')->only(['guardar', 'eliminar']);
    }

    public function listar($incidenteId)
    {
        $incidente = Incidente::findOrFail($incidenteId);

        return response()->json($incidente->comentarios()->with('autor')->get());
    }

    public function guardar(Request $request, $incidenteId)
    {
        $incidente = Incidente::findOrFail($incidenteId);

        $datos = $this->validate($request, [
            'comentario' => 'required',
            'es_interno' => 'nullable|boolean',
        ]);

        $comentario = ComentarioIncidente::create([
            'incidente_id' => $incidente->id,
            'usuario_id' => auth('admin')->id(),
            'comentario' => $datos['comentario'],
            'es_interno' => $datos['es_interno'] ?? true,
        ]);

        return response()->json($comentario->load('autor'), 201);
    }

    public function eliminar($incidenteId, $id)
    {
        ComentarioIncidente::where('incidente_id', $incidenteId)->findOrFail($id)->delete();

        return response()->json('ok');
    }
}
