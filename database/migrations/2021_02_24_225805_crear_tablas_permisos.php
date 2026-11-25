<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas de roles y permisos (paquete spatie/laravel-permission).
 *
 * Los nombres de las tablas y la columna del modelo (modelo_id) se configuran en
 * config/permission.php. Las columnas name, guard_name, role_id, permission_id y
 * model_type están fijas dentro del paquete y deben conservar su nombre.
 */
class CrearTablasPermisos extends Migration
{
    public function up()
    {
        $tablas = config('permission.table_names');
        $columnas = config('permission.column_names');

        if (empty($tablas)) {
            throw new \Exception('Error: config/permission.php no está cargado. Ejecute [php artisan config:clear] e intente nuevamente.');
        }

        Schema::create($tablas['permissions'], function (Blueprint $tabla) {
            $tabla->bigIncrements('id');
            $tabla->string('name');
            $tabla->string('guard_name');
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();

            $tabla->unique(['name', 'guard_name']);
        });

        Schema::create($tablas['roles'], function (Blueprint $tabla) {
            $tabla->bigIncrements('id');
            $tabla->string('name');
            $tabla->string('guard_name');
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();

            $tabla->unique(['name', 'guard_name']);
        });

        Schema::create($tablas['model_has_permissions'], function (Blueprint $tabla) use ($tablas, $columnas) {
            $tabla->unsignedBigInteger('permission_id');

            $tabla->string('model_type');
            $tabla->unsignedBigInteger($columnas['model_morph_key']);
            $tabla->index([$columnas['model_morph_key'], 'model_type'], 'modelo_tiene_permisos_modelo_index');

            $tabla->foreign('permission_id')
                ->references('id')
                ->on($tablas['permissions'])
                ->onDelete('cascade');

            $tabla->primary(['permission_id', $columnas['model_morph_key'], 'model_type'],
                'modelo_tiene_permisos_primary');
        });

        Schema::create($tablas['model_has_roles'], function (Blueprint $tabla) use ($tablas, $columnas) {
            $tabla->unsignedBigInteger('role_id');

            $tabla->string('model_type');
            $tabla->unsignedBigInteger($columnas['model_morph_key']);
            $tabla->index([$columnas['model_morph_key'], 'model_type'], 'modelo_tiene_roles_modelo_index');

            $tabla->foreign('role_id')
                ->references('id')
                ->on($tablas['roles'])
                ->onDelete('cascade');

            $tabla->primary(['role_id', $columnas['model_morph_key'], 'model_type'],
                'modelo_tiene_roles_primary');
        });

        Schema::create($tablas['role_has_permissions'], function (Blueprint $tabla) use ($tablas) {
            $tabla->unsignedBigInteger('permission_id');
            $tabla->unsignedBigInteger('role_id');

            $tabla->foreign('permission_id')
                ->references('id')
                ->on($tablas['permissions'])
                ->onDelete('cascade');

            $tabla->foreign('role_id')
                ->references('id')
                ->on($tablas['roles'])
                ->onDelete('cascade');

            $tabla->primary(['permission_id', 'role_id'], 'rol_tiene_permisos_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down()
    {
        $tablas = config('permission.table_names');

        if (empty($tablas)) {
            throw new \Exception('Error: config/permission.php no está cargado. Publique la configuración del paquete o elimine las tablas manualmente.');
        }

        Schema::drop($tablas['role_has_permissions']);
        Schema::drop($tablas['model_has_roles']);
        Schema::drop($tablas['model_has_permissions']);
        Schema::drop($tablas['roles']);
        Schema::drop($tablas['permissions']);
    }
}
