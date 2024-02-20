<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisKategoriTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('kategori'))
            Schema::create('kategori', function (Blueprint $table) {
                $table->increments('id');
                $table->string('kategori')->unique();
                $table->text('aciklama');
                $table->integer('post_type_id');
                $table->integer('website_id');
                $table->string('meta_title')->nullable();
                $table->string('meta_desc')->nullable();
                $table->string('keywords')->nullable();
                $table->string('slug');
                $table->uuid('user_ID');
                $table->integer('dil_ID')->default(1);
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
        Schema::dropIfExists('kategori');
    }
}
