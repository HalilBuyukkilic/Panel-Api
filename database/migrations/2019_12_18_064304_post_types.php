<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PostTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('post_types'))
            Schema::create('post_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('title_plural');
                $table->string('slug');
                $table->integer('website_id');
                $table->boolval('seo_enabled');
                $table->boolval('media_enabled');
                $table->boolval('summary_enabled');
                $table->boolval('tag_enabled');
                $table->boolval('category_enabled');
                $table->boolval('language_enabled');
                $table->boolval('author_enabled');
                $table->string('slug-en')->nullable();
                $table->string('slug-ar')->nullable();
                $table->string('folder-path')->nullable();
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
        Schema::dropIfExists('post_types');
    }
}
