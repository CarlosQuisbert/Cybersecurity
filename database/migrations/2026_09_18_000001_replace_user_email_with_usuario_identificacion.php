<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceUserEmailWithUsuarioIdentificacion extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('usuario')->nullable()->unique()->after('name');
            $table->string('identificacion')->nullable()->unique()->after('usuario');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'email_verified_at']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['usuario']);
            $table->dropUnique(['identificacion']);
            $table->dropColumn(['usuario', 'identificacion']);
        });
    }
}
