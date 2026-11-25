<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function listar()
    {
        $roles = Rol::with('permisos')->orderByDesc('id')->get();

        $permisos = Permiso::all();

        return view('panel.roles.listado', compact('roles', 'permisos'));
    }

    public function guardar(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|unique:roles,name',
            'permisos' => 'required',
        ]);

        $rol = new Rol;
        $rol->name = $request->nombre;
        $rol->guard_name = 'admin';
        $rol->save();

        if ($request->filled('permisos')) {
            $rol->syncPermissions($request->permisos);
        }

        return $rol->load('permisos');
    }

    public function actualizar(Request $request, $id)
    {
        $this->validate($request, [
            'nombre' => 'required',
        ]);

        $rol = Rol::findOrFail($id);
        $rol->name = $request->nombre;
        $rol->save();

        if ($request->filled('permisos')) {
            $rol->syncPermissions($request->permisos);
        } else {
            $rol->permissions()->detach();
        }

        return Rol::with('permisos')->findOrFail($id);
    }

    public function eliminar($id)
    {
        Rol::findOrFail($id)->delete();

        return response()->json('ok');
    }
}
