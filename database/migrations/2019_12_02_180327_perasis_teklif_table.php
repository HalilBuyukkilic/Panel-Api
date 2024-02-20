<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerasisTeklifTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('teklif'))
            Schema::create('teklif', function (Blueprint $table) {
                $table->increments('id');
                $table->string('fullname');
                $table->string('email')->unique();
                $table->string('telefon');
                $table->string('product');
                $table->string('not');
                $table->string('slug');
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
        Schema::dropIfExists('teklif');
    }
}
