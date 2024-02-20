<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('role_user'))
            Schema::create('role_user', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('user_ID');
                //$table->integer('role_ID')->unsigned();
                $table->integer('role_ID');
                $table->foreign('user_ID')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('role_ID')->references('id')->on('roles')->onDelete('cascade');   
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
