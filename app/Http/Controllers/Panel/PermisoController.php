<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:roles.gestionar,admin']);
    }

    public function listar()
    {
        $permisos = Permiso::orderByDesc('id')->get();

        return view('panel.permisos.listado', compact('permisos'));
    }

    public function guardar(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|max:50|unique:permisos,name',
        ]);

        return Permiso::create([
            'name' => $request->nombre,
            'guard_name' => 'admin',
        ]);
    }

    public function actualizar(Request $request, $id)
    {
        $this->validate($request, [
            'nombre' => 'required|max:50',
        ]);

        $permiso = Permiso::findOrFail($id);

        $permiso->update(['name' => $request->nombre]);

        return $permiso->fresh();
    }

    public function eliminar($id)
    {
        Permiso::findOrFail($id)->delete();

        return response()->json('ok');
    }
}
