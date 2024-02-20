<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisDilTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('dil'))
            Schema::create('dil', function (Blueprint $table) {
                $table->increments('id');
                $table->string('dil');
                $table->text('aciklama');
                $table->integer('website_id');
                $table->timestamps();
                $table->string('meta_title')->nullable();
                $table->string('meta_desc')->nullable();
                $table->string('slug')->unique();
                $table->uuid('user_ID');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dil');
    }
}
