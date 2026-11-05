<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentsTable extends Migration
{
    public function up()
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('incident_type_id')->constrained('incident_types')->onDelete('restrict');
            $table->enum('severity', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->enum('status', ['abierto', 'en_proceso', 'contenido', 'erradicado', 'recuperado', 'cerrado'])->default('abierto');
            $table->string('reported_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('admins')->onDelete('set null');
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('contained_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('affected_assets')->nullable();
            $table->text('impact')->nullable();
            $table->text('remediation')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index('incident_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidents');
    }
}
