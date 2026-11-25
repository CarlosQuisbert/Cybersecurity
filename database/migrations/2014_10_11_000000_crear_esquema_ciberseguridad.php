<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CrearEsquemaCiberseguridad extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * @return void
     */
    public function up()
    {
        $schema = config('database.connections.pgsql.schema');
        $schema = is_array($schema) ? $schema[0] : $schema;
        $schema = $schema ?: env('DB_SCHEMA', 'cybersecurity');

        DB::statement('CREATE SCHEMA IF NOT EXISTS "' . str_replace('"', '""', $schema) . '"');
    }

    /**
     * Revierte la migración.
     *
     * @return void
     */
    public function down()
    {
        // No se elimina el schema en rollback para no perder datos.
    }
}
