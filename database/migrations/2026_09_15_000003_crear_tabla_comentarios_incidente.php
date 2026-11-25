<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaComentariosIncidente extends Migration
{
    public function up()
    {
        Schema::create('comentarios_incidente', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('incidente_id')->constrained('incidentes')->onDelete('cascade');
            $tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->text('comentario');
            $tabla->boolean('es_interno')->default(true);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();

            $tabla->index('incidente_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comentarios_incidente');
    }
}
