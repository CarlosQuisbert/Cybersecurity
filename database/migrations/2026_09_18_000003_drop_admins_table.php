<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAdminsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('admins');
    }

    public function down()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('usuario')->nullable()->unique();
            $table->string('identificacion')->nullable()->unique();
            $table->string('password');
            $table->boolean('status')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
