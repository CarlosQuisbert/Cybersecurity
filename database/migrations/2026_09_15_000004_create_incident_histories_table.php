<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('incident_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->foreign('changed_by')->references('id')->on('admins')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('incident_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_histories');
    }
}
