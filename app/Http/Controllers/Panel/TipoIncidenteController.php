<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\TipoIncidente;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipoIncidenteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:tipos-incidente.gestionar,admin');
    }

    public function listar()
    {
        $tipos = TipoIncidente::withCount('incidentes')->orderByDesc('creado_en')->orderByDesc('id')->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($tipos);
        }

        return view('panel.tipos_incidente.listado', compact('tipos'));
    }

    public function guardar(Request $request)
    {
        $datos = $this->validate($request, [
            'nombre' => 'required|max:100|unique:tipos_incidente,nombre',
            'descripcion' => 'nullable',
            'activo' => 'nullable|boolean',
        ]);

        $datos['slug'] = Str::slug($datos['nombre']);
        $datos['activo'] = $request->has('activo') ? (bool) $request->activo : true;

        return response()->json(TipoIncidente::create($datos), 201);
    }

    public function actualizar(Request $request, $id)
    {
        $tipo = TipoIncidente::findOrFail($id);

        $datos = $this->validate($request, [
            'nombre' => 'required|max:100|unique:tipos_incidente,nombre,' . $id,
            'descripcion' => 'nullable',
            'activo' => 'nullable|boolean',
        ]);

        $datos['slug'] = Str::slug($datos['nombre']);
        $tipo->update($datos);

        return response()->json($tipo->fresh());
    }

    public function eliminar($id)
    {
        $tipo = TipoIncidente::findOrFail($id);

        if ($tipo->incidentes()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar: tiene incidentes asociados.'], 422);
        }

        $tipo->delete();

        return response()->json('ok');
    }
}
