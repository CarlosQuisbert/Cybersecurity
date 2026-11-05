<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('incident_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->text('comment');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();

            $table->index('incident_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_comments');
    }
}
