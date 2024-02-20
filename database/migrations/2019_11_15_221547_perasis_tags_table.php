<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tags'))
            Schema::create('tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tag_name');
                $table->string('slug'); 
                $table->integer('website_id');
                $table->string('meta_title')->nullable(); 
                $table->string('meta_desc')->nullable();  
                $table->string('keywords')->nullable();   
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
        Schema::dropIfExists('Tags');
    }
}
