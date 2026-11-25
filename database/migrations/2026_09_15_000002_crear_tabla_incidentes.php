<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaIncidentes extends Migration
{
    public function up()
    {
        Schema::create('incidentes', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('codigo', 20)->unique();
            $tabla->string('titulo');
            $tabla->text('descripcion');
            $tabla->foreignId('tipo_incidente_id')->constrained('tipos_incidente')->onDelete('restrict');
            $tabla->enum('severidad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $tabla->enum('estado', ['abierto', 'en_proceso', 'contenido', 'erradicado', 'recuperado', 'cerrado'])->default('abierto');
            $tabla->string('reportado_por')->nullable();
            $tabla->foreignId('asignado_a')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->timestamp('detectado_en')->nullable();
            $tabla->timestamp('contenido_en')->nullable();
            $tabla->timestamp('resuelto_en')->nullable();
            $tabla->timestamp('cerrado_en')->nullable();
            $tabla->text('activos_afectados')->nullable();
            $tabla->text('impacto')->nullable();
            $tabla->text('remediacion')->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();

            $tabla->index(['estado', 'severidad']);
            $tabla->index('tipo_incidente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidentes');
    }
}
