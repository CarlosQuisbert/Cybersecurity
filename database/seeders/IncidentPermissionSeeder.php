<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IncidentPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'dashboard.view',
            'incidents.view',
            'incidents.create',
            'incidents.edit',
            'incidents.delete',
            'incident-types.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        $role = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'admin']);
        $role->syncPermissions($permissions);

        User::whereIn('usuario', ['john.doe', 'desmond.ava'])->get()->each(function ($user) use ($role) {
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        });
    }
}
