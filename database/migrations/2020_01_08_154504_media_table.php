<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('media'))
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('dimensions', 255)->nullable();
            $table->string('fileformat', 255)->nullable();
            $table->string('filesize', 255)->nullable();
            $table->string('tag_title', 255)->nullable();
            $table->string('tag_alt', 255)->nullable();
            $table->integer('post_id')->nullable();
            $table->integer('media_type_id')->nullable();
            $table->integer('website_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
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
        Schema::dropIfExists('media');
    }
}
