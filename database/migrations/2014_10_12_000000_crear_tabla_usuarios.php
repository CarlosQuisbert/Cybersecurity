<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaUsuarios extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre');
            $tabla->string('usuario')->unique();
            $tabla->string('identificacion')->unique();
            $tabla->string('contrasena');
            $tabla->string('token_recordar', 100)->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}
