<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('website_menus'))
        Schema::create('website_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('website_id');
            $table->integer('parent_menu_id')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('link')->nullable();
            $table->integer('menu_order')->nullable();
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
        Schema::dropIfExists('website_menus');
    }
}
