<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebsiteOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {  
         if (!Schema::hasTable('website_options'))
            Schema::create('website_options', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('website_id');
                $table->string('name')->nullable();
                $table->string('value')->nullable();
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
        Schema::dropIfExists('website_options');
    }
}
