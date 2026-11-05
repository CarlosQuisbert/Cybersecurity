<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $users = User::with('roles')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [

            'name' => 'required|string|max:255',
            'usuario' => 'required|string|max:60|regex:/^[A-Za-z0-9._-]+$/|unique:users,usuario',
            'identificacion' => 'required|string|max:20|unique:users,identificacion',
            'role' => 'nullable|exists:roles,id',

        ]);

        $user = new User;

        $user->name = $request->name;

        $user->usuario = strtolower($request->usuario);

        $user->identificacion = $request->identificacion;

        $user->password = bcrypt($request->identificacion);

        $user->save();

        if ($request->filled('role')) $user->syncRoles([$request->role]);

        return redirect(route('users.index'))->with('message', 'El usuario se creó correctamente. Usuario: ' . $user->usuario);
    }

    public function edit($id)
    {
        $user = User::find($id);

        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [

            'name' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20|unique:users,identificacion,' . $user->id,
            'role' => 'nullable|exists:roles,id',

        ]);

        $user->name = $request->name;

        if ($user->identificacion !== $request->identificacion) {
            $user->identificacion = $request->identificacion;
            $user->password = bcrypt($request->identificacion);
        }

        $user->save();

        if ($request->filled('role')) {

            $user->syncRoles([$request->role]);

        }else {
            $user->roles()->detach();
        }

        return redirect(route('users.index'))->with('message_update', 'El usuario se actualizó correctamente.');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('message_delete', 'El usuario se eliminó correctamente.');
    }

}
