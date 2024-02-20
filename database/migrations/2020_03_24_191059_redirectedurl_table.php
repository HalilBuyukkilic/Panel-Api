<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RedirectedurlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {  
        if(!Schema::hasTable('redirectedurl'))
        Schema::create('redirectedurl', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('redirect_post_id');
                $table->string('title')->nullable();
                $table->string('urldesc')->nullable();
                $table->string('slug');
                $table->integer('website_id');
                $table->integer('post_type_id');
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
         Schema::dropIfExists('redirectedurl');
    }
}
