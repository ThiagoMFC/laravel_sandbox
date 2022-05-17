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
        Schema::create('uno_games', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('user_token');
            $table->string('status');
            $table->string('result');
            $table->text('player0');
            $table->integer('player0points');
            $table->text('player1');
            $table->integer('player1points');
            $table->text('player2');
            $table->integer('player2points');
            $table->text('player3');
            $table->integer('player3points');
            $table->text('deck');
            $table->text('pile');
            $table->integer('turns');
            $table->string('direction');
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
        Schema::dropIfExists('uno_games');
    }
};
