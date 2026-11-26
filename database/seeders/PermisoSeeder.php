<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Permisos y roles según los actores de la tesis (apartado 3.3.2.3):
 * Analista, Responsable de seguridad y Administrador, más el Reportante
 * que solo registra eventos (control 6.8 de la ISO 27002).
 */
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
            'incidentes.evaluar',
            'incidentes.asignar',
            'evidencias.adjuntar',
            'tipos-incidente.gestionar',
            'activos.gestionar',
            'modelo.configurar',
            'usuarios.gestionar',
            'roles.gestionar',
            'bitacora.ver',
        ];

        foreach ($permisos as $nombre) {
            Permiso::firstOrCreate(['name' => $nombre, 'guard_name' => 'admin']);
        }

        $roles = [
            'Administrador' => $permisos,
            'Responsable de seguridad' => [
                'panel.ver', 'incidentes.ver', 'incidentes.crear', 'incidentes.editar',
                'incidentes.evaluar', 'incidentes.asignar', 'evidencias.adjuntar',
                'activos.gestionar', 'bitacora.ver',
            ],
            'Analista' => [
                'panel.ver', 'incidentes.ver', 'incidentes.crear', 'incidentes.editar',
                'incidentes.evaluar', 'evidencias.adjuntar',
            ],
            'Reportante' => [
                'panel.ver', 'incidentes.ver', 'incidentes.crear',
            ],
        ];

        foreach ($roles as $nombre => $permisosRol) {
            Rol::firstOrCreate(['name' => $nombre, 'guard_name' => 'admin'])->syncPermissions($permisosRol);
        }

        $administrador = Rol::where('name', 'Administrador')->first();
        Usuario::whereIn('usuario', ['gabriela.casas', 'carlos.quisbert'])->get()->each(function ($usuario) use ($administrador) {
            if (! $usuario->hasRole($administrador)) {
                $usuario->assignRole($administrador);
            }
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
