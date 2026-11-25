<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaHistorialIncidentes extends Migration
{
    public function up()
    {
        Schema::create('historial_incidentes', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('incidente_id')->constrained('incidentes')->onDelete('cascade');
            $tabla->string('estado_anterior')->nullable();
            $tabla->string('estado_nuevo');
            $tabla->foreignId('cambiado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->text('notas')->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();

            $tabla->index('incidente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_incidentes');
    }
}
