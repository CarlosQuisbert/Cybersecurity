<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use App\Servicios\Bitacora;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:usuarios.gestionar,admin']);
    }

    public function listar()
    {
        $usuarios = Usuario::with('roles')->orderByDesc('id')->get();

        return view('panel.usuarios.listado', compact('usuarios'));
    }

    public function crear()
    {
        $roles = Rol::all();

        return view('panel.usuarios.crear', compact('roles'));
    }

    public function guardar(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|max:60|regex:/^[A-Za-z0-9._-]+$/|unique:usuarios,usuario',
            'identificacion' => 'required|string|max:20|unique:usuarios,identificacion',
            'rol' => 'nullable|exists:roles,id',
        ]);

        $usuario = new Usuario;
        $usuario->nombre = $request->nombre;
        $usuario->usuario = strtolower($request->usuario);
        $usuario->identificacion = $request->identificacion;
        $usuario->contrasena = bcrypt($request->identificacion);
        $usuario->save();

        if ($request->filled('rol')) $usuario->syncRoles([$request->rol]);

        Bitacora::registrar('usuario.crear', 'usuario', $usuario->id, ['usuario' => $usuario->usuario, 'rol' => $request->input('rol')]);

        return redirect(route('usuarios.listar'))->with('mensaje', 'El usuario se creó correctamente. Usuario: ' . $usuario->usuario);
    }

    public function editar($id)
    {
        $usuario = Usuario::findOrFail($id);

        $roles = Rol::all();

        return view('panel.usuarios.editar', compact('usuario', 'roles'));
    }

    public function actualizar(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20|unique:usuarios,identificacion,' . $usuario->id,
            'rol' => 'nullable|exists:roles,id',
        ]);

        $usuario->nombre = $request->nombre;

        if ($usuario->identificacion !== $request->identificacion) {
            $usuario->identificacion = $request->identificacion;
            $usuario->contrasena = bcrypt($request->identificacion);
        }

        $usuario->save();

        if ($request->filled('rol')) {
            $usuario->syncRoles([$request->rol]);
        } else {
            $usuario->roles()->detach();
        }

        Bitacora::registrar('usuario.editar', 'usuario', $id, ['rol' => $request->input('rol')]);

        return redirect(route('usuarios.listar'))->with('mensaje_actualizado', 'El usuario se actualizó correctamente.');
    }

    public function eliminar($id)
    {
        Usuario::findOrFail($id)->delete();

        Bitacora::registrar('usuario.eliminar', 'usuario', $id);

        return back()->with('mensaje_eliminado', 'El usuario se eliminó correctamente.');
    }
}
