<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clasificación de doble eje (categoría + táctica), origen, activo del inventario,
 * evaluaciones multicriterio, evidencias y bitácora de acciones.
 *
 * La "severidad" elegida a mano se elimina: la prioridad pasa a ser calculada
 * por el modelo (índice de riesgo y nivel de prioridad).
 */
class AgregarClasificacionYEvaluacionAIncidentes extends Migration
{
    public function up()
    {
        Schema::table('incidentes', function (Blueprint $tabla) {
            $tabla->dropIndex(['estado', 'severidad']);
        });

        Schema::table('incidentes', function (Blueprint $tabla) {
            $tabla->dropColumn('severidad');
            // Evento por calificar, incidente confirmado o evento descartado (apartado 2.1.3).
            $tabla->string('calificacion', 20)->default('incidente');
            $tabla->string('origen', 20)->nullable();
            $tabla->foreignId('activo_id')->nullable()->constrained('activos')->onDelete('set null');
            // Último resultado del motor de riesgo (copia para ordenar la cola).
            $tabla->decimal('indice_riesgo', 6, 2)->nullable();
            $tabla->string('nivel_prioridad', 10)->nullable();
            $tabla->unsignedSmallInteger('progresion_nivel')->nullable();
            $tabla->timestamp('evaluado_en')->nullable();

            $tabla->index('estado');
            $tabla->index(['nivel_prioridad', 'indice_riesgo']);
        });

        Schema::create('incidente_tactica', function (Blueprint $tabla) {
            $tabla->foreignId('incidente_id')->constrained('incidentes')->onDelete('cascade');
            $tabla->foreignId('tactica_id')->constrained('tacticas')->onDelete('restrict');
            $tabla->primary(['incidente_id', 'tactica_id']);
        });

        // Cada evaluación guarda el resultado y la versión de configuración usada (3.3.2.5).
        Schema::create('evaluaciones', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('incidente_id')->constrained('incidentes')->onDelete('cascade');
            $tabla->foreignId('configuracion_id')->constrained('configuraciones_modelo')->onDelete('restrict');
            $tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->decimal('indice_riesgo', 6, 2);
            $tabla->string('nivel_prioridad', 10);
            $tabla->string('origen', 20);
            $tabla->unsignedSmallInteger('progresion_nivel')->nullable();
            $tabla->boolean('progresion_excluida')->default(false);
            $tabla->boolean('omision_inventario')->default(false);
            $tabla->string('motivo')->nullable();
            $tabla->json('detalle');
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
            $tabla->index(['incidente_id', 'creado_en']);
        });

        // Nivel elegido por el analista en cada escala.
        Schema::create('valoraciones', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('evaluacion_id')->constrained('evaluaciones')->onDelete('cascade');
            $tabla->foreignId('escala_id')->constrained('escalas')->onDelete('restrict');
            $tabla->unsignedSmallInteger('nivel');
            $tabla->unique(['evaluacion_id', 'escala_id']);
        });

        // Evidencias con trazabilidad (control 5.28 de la ISO 27002).
        Schema::create('evidencias_incidente', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('incidente_id')->constrained('incidentes')->onDelete('cascade');
            $tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->string('nombre_original');
            $tabla->string('ruta');
            $tabla->string('tipo_mime', 150)->nullable();
            $tabla->unsignedBigInteger('tamano');
            $tabla->string('hash_sha256', 64);
            $tabla->text('descripcion')->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        // Registro de las acciones de cada usuario (RNF1).
        Schema::create('bitacora', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->string('accion', 80);
            $tabla->string('entidad', 60)->nullable();
            $tabla->unsignedBigInteger('entidad_id')->nullable();
            $tabla->json('detalle')->nullable();
            $tabla->string('ip', 45)->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->index(['entidad', 'entidad_id']);
            $tabla->index('creado_en');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bitacora');
        Schema::dropIfExists('evidencias_incidente');
        Schema::dropIfExists('valoraciones');
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('incidente_tactica');

        Schema::table('incidentes', function (Blueprint $tabla) {
            $tabla->dropIndex(['nivel_prioridad', 'indice_riesgo']);
            $tabla->dropIndex(['estado']);
            $tabla->dropConstrainedForeignId('activo_id');
            $tabla->dropColumn(['calificacion', 'origen', 'indice_riesgo', 'nivel_prioridad', 'progresion_nivel', 'evaluado_en']);
            $tabla->enum('severidad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $tabla->index(['estado', 'severidad']);
        });
    }
}
