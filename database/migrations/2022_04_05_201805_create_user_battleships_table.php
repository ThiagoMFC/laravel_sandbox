<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_battleships', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('user_token');
            $table->string('status');
            $table->string('result');
            $table->text('ships');
            $table->integer('hits');
            $table->integer('misses');
            $table->timestamp('date_started', $precision=0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_battleships');
    }
};
