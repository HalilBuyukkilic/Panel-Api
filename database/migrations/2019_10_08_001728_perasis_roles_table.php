<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('roles'))
            Schema::create('roles', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('role')->unique();
                $table->text('aciklama')->nullable();
                $table->string('slug');
                $table->uuid('user_ID');
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
