<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal (Laravel exige el nombre DatabaseSeeder para "db:seed").
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UsuarioSeeder::class);
        $this->call(IncidenteSeeder::class);
        $this->call(PermisoSeeder::class);
    }
}
