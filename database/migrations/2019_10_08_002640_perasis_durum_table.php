<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisDurumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('durum'))
            Schema::create('durum', function (Blueprint $table) {
                $table->increments('id');
                $table->string('durum')->unique();
                $table->text('aciklama');
                $table->string('durum2');
                $table->text('aciklama2');
                $table->timestamps();
                $table->uuid('user_ID');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('durum');
    }
}
