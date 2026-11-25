<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    public function run()
    {
        $permisos = [
            'panel.ver',
            'incidentes.ver',
            'incidentes.crear',
            'incidentes.editar',
            'incidentes.eliminar',
            'tipos-incidente.gestionar',
        ];

        foreach ($permisos as $nombre) {
            Permiso::firstOrCreate(['name' => $nombre, 'guard_name' => 'admin']);
        }

        $rol = Rol::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'admin']);
        $rol->syncPermissions($permisos);

        Usuario::whereIn('usuario', ['john.doe', 'desmond.ava'])->get()->each(function ($usuario) use ($rol) {
            if (! $usuario->hasRole($rol)) {
                $usuario->assignRole($rol);
            }
        });
    }
}
