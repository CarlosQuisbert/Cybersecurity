<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceAdminEmailWithUsuarioIdentificacion extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('usuario')->nullable()->unique()->after('name');
            $table->string('identificacion')->nullable()->unique()->after('usuario');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['usuario']);
            $table->dropUnique(['identificacion']);
            $table->dropColumn(['usuario', 'identificacion']);
        });
    }
}
