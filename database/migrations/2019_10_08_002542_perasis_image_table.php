<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('image'))
            Schema::create('image', function (Blueprint $table) {
                $table->increments('id');
                $table->string('imageName');
                $table->string('imageUrl');
                $table->integer('image_ID')->nullable();
                $table->integer('sayfa_ID')->nullable();
                $table->uuid('avatarID')->nullable();
                $table->string('tag_title')->nullable();
                $table->string('tag_alt')->nullable();
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
        Schema::dropIfExists('image');
    }
}
