<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterGamesPlayedDataGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_games_played_data_groups', function (Blueprint $table) {
            $table->id('master_games_played_data_groups_id');
            $table->unsignedBigInteger('type_value');
            $table->integer('stack_size')->default(0);
            $table->double('season');
            $table->tinyInteger('game_type');
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->integer('win')->nullable();
            $table->integer('loss')->nullable();
            $table->integer('games_played')->nullable();
            $table->integer('win_leaderboard')->nullable();
            $table->integer('loss_leaderboard')->nullable();
            $table->integer('games_played_leaderboard')->nullable();
            $table->timestamps(); // Add this line if you want timestamps

            $table->unique(['type_value', 'stack_size', 'season', 'game_type', 'blizz_id', 'region'], 'UNIQUE');
            $table->index('games_played');
            $table->index(['season', 'game_type', 'games_played'], 'index1');
            $table->index(['type_value', 'season', 'game_type', 'region'], 'primaryMinusBlizzID');
            $table->index(['type_value', 'stack_size', 'season', 'game_type', 'blizz_id', 'region', 'games_played'], 'total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_games_played_data_groups');
    }
}
