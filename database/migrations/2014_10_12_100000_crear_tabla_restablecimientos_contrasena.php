<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla usada por el "broker" de restablecimiento de contraseñas de Laravel.
 * Las columnas (email, token, created_at) las exige el framework y no pueden renombrarse.
 */
class CrearTablaRestablecimientosContrasena extends Migration
{
    public function up()
    {
        Schema::create('restablecimientos_contrasena', function (Blueprint $tabla) {
            $tabla->string('email')->index();
            $tabla->string('token');
            $tabla->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('restablecimientos_contrasena');
    }
}
