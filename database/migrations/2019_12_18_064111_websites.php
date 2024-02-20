<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Websites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('websites'))
            Schema::create('websites', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('slug');
                $table->string('url');
                $table->string('target_folder')->nullable();
                $table->uuid('created_by')->nullable();
                $table->integer('status_id')->default(1)->nullable();
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
        Schema::dropIfExists('websites');
    }
}
