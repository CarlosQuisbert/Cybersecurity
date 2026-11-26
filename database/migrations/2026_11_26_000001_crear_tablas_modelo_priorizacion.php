<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parámetros del modelo de priorización (apartado 2.7.2 de la tesis):
 * criterios, pesos, escalas, umbrales y catálogo de tácticas viven en la
 * base de datos y no en el código fuente.
 */
class CrearTablasModeloPriorizacion extends Migration
{
    public function up()
    {
        // Segundo nivel de la jerarquía AHP: los cuatro bloques (apartado 3.3.1.2).
        Schema::create('bloques_criterio', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('codigo', 40)->unique();
            $tabla->string('nombre');
            $tabla->unsignedSmallInteger('orden')->default(0);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        // Tercer nivel: los nueve criterios, con su trazabilidad documental (Tabla 3.2).
        Schema::create('criterios', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('bloque_id')->constrained('bloques_criterio')->onDelete('restrict');
            $tabla->string('codigo', 40)->unique();
            $tabla->string('nombre');
            $tabla->text('descripcion')->nullable();
            $tabla->text('fundamento')->nullable();
            // analista: lo valora el analista; inventario: sale del inventario de activos;
            // tacticas: sale de la táctica ATT&CK más avanzada.
            $tabla->string('fuente', 20)->default('analista');
            // Cómo se combinan las subescalas del criterio (alcance = el mayor de los niveles).
            $tabla->string('combinacion', 20)->default('unica');
            $tabla->boolean('solo_adversario')->default(false);
            $tabla->unsignedSmallInteger('orden')->default(0);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        // Escalas de valoración (a-j del apartado 3.3.1.4). El alcance tiene dos.
        Schema::create('escalas', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('criterio_id')->constrained('criterios')->onDelete('cascade');
            $tabla->string('codigo', 40)->unique();
            $tabla->string('nombre');
            $tabla->text('nota')->nullable();
            $tabla->unsignedSmallInteger('orden')->default(0);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        Schema::create('niveles_escala', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('escala_id')->constrained('escalas')->onDelete('cascade');
            $tabla->unsignedSmallInteger('nivel');
            $tabla->text('descriptor');
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
            $tabla->unique(['escala_id', 'nivel']);
        });

        // Configuración versionada: pesos, umbrales, capacidad y versión de ATT&CK.
        Schema::create('configuraciones_modelo', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->unsignedInteger('version')->unique();
            $tabla->text('descripcion')->nullable();
            $tabla->string('version_attack', 40);
            $tabla->decimal('umbral_critico', 5, 2);
            $tabla->decimal('umbral_alto', 5, 2);
            $tabla->decimal('umbral_medio', 5, 2);
            $tabla->unsignedSmallInteger('capacidad_criticos')->default(3);
            $tabla->boolean('vigente')->default(false);
            $tabla->json('origen_pesos')->nullable();
            $tabla->foreignId('creado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        Schema::create('pesos_bloque', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('configuracion_id')->constrained('configuraciones_modelo')->onDelete('cascade');
            $tabla->foreignId('bloque_id')->constrained('bloques_criterio')->onDelete('restrict');
            $tabla->decimal('peso', 9, 6);
            $tabla->unique(['configuracion_id', 'bloque_id']);
        });

        Schema::create('pesos_criterio', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('configuracion_id')->constrained('configuraciones_modelo')->onDelete('cascade');
            $tabla->foreignId('criterio_id')->constrained('criterios')->onDelete('restrict');
            $tabla->decimal('peso_local', 9, 6);
            $tabla->unique(['configuracion_id', 'criterio_id']);
        });

        // Catálogo de tácticas de MITRE ATT&CK con su nivel de progresión (escala j).
        Schema::create('tacticas', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('identificador', 12)->unique();
            $tabla->string('nombre');
            $tabla->text('objetivo')->nullable();
            $tabla->unsignedSmallInteger('nivel_progresion');
            $tabla->unsignedSmallInteger('orden')->default(0);
            $tabla->boolean('activo')->default(true);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });

        // Inventario de activos valorado (insumo del criterio de criticidad, escala d).
        Schema::create('activos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('codigo', 40)->unique();
            $tabla->string('nombre');
            $tabla->string('tipo', 60)->nullable();
            $tabla->string('area')->nullable();
            $tabla->text('descripcion')->nullable();
            $tabla->unsignedSmallInteger('criticidad');
            $tabla->boolean('activo')->default(true);
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activos');
        Schema::dropIfExists('tacticas');
        Schema::dropIfExists('pesos_criterio');
        Schema::dropIfExists('pesos_bloque');
        Schema::dropIfExists('configuraciones_modelo');
        Schema::dropIfExists('niveles_escala');
        Schema::dropIfExists('escalas');
        Schema::dropIfExists('criterios');
        Schema::dropIfExists('bloques_criterio');
    }
}
