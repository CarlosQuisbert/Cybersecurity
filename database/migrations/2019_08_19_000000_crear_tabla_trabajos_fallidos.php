<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla usada por las colas de Laravel para registrar trabajos fallidos.
 * Las columnas las exige el framework y no pueden renombrarse.
 */
class CrearTablaTrabajosFallidos extends Migration
{
    public function up()
    {
        Schema::create('trabajos_fallidos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('uuid')->unique();
            $tabla->text('connection');
            $tabla->text('queue');
            $tabla->longText('payload');
            $tabla->longText('exception');
            $tabla->timestamp('failed_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trabajos_fallidos');
    }
}
