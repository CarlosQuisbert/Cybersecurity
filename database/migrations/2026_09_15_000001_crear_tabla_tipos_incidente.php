<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaTiposIncidente extends Migration
{
    public function up()
    {
        Schema::create('tipos_incidente', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre')->unique();
            $tabla->string('slug')->unique()->nullable();
            $tabla->text('descripcion')->nullable();
            $tabla->boolean('activo')->default(true);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipos_incidente');
    }
}
