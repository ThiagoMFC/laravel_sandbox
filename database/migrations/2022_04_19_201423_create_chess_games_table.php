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
        Schema::create('chess_games', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('user_token');
            $table->string('status');
            $table->string('result');
            $table->text('white_pieces');
            $table->text('black_pieces');
            $table->integer('turns');
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
        Schema::dropIfExists('chess_games');
    }
};
