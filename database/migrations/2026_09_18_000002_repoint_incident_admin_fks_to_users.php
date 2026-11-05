<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepointIncidentAdminFksToUsers extends Migration
{
    public function up()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('incident_comments', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('incident_histories', function (Blueprint $table) {
            $table->dropForeign(['changed_by']);
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->foreign('assigned_to')->references('id')->on('admins')->onDelete('set null');
        });

        Schema::table('incident_comments', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });

        Schema::table('incident_histories', function (Blueprint $table) {
            $table->dropForeign(['changed_by']);
            $table->foreign('changed_by')->references('id')->on('admins')->onDelete('set null');
        });
    }
}
