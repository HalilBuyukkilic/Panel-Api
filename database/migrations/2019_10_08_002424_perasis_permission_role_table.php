<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisPermissionRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permission_role'))
            Schema::create('permission_role', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('role_ID');
                $table->integer('permission_ID');
                $table->foreign('role_ID')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('permission_ID')->references('id')->on('permissions')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_role');
    }
}
