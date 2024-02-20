<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Posts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('posts'))
            Schema::create('posts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('summary')->nullable();
                $table->text('content')->nullable();
                $table->integer('category_id')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_desc')->nullable();
                $table->string('keywords')->nullable();
                $table->string('tags')->nullable();
                $table->integer('status_id')->nullable();
                $table->integer('language_id');
                $table->integer('post_type_id');
                $table->integer('website_id');
                $table->integer('media_id')->nullable();
                $table->uuid('approved_by')->nullable();
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->timestamp('published_at')->nullable();
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
        Schema::dropIfExists('posts');
    }
}
