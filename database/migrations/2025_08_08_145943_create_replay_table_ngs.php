<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayTableNgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ngs.replay', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->increments('replayID');
            $table->string('tournament', 45)->nullable()->default('NGS');
            $table->string('season', 45)->nullable();
            $table->string('division_0', 45)->nullable();
            $table->string('division_1', 45)->nullable();
            $table->string('team_0_name', 200)->nullable();
            $table->string('team_1_name', 200)->nullable();
            $table->string('round', 45)->nullable();
            $table->integer('game')->nullable();
            $table->tinyInteger('first_pick')->nullable();
            $table->dateTime('game_date');
            $table->unsignedSmallInteger('game_length');
            $table->unsignedTinyInteger('game_map');
            $table->string('team_0_map_ban', 45)->nullable();
            $table->string('team_0_map_ban_2', 45)->nullable();
            $table->string('team_1_map_ban', 45)->nullable();
            $table->string('team_1_map_ban_2', 45)->nullable();
            $table->string('game_version', 32);
            $table->unsignedTinyInteger('region');
            $table->dateTime('date_added');

            $table->index('game_date', 'game_date');
            $table->index(['tournament', 'season', 'division_0', 'division_1', 'team_0_name', 'team_1_name', 'round'], 'index');
            $table->index('region', 'region');
            $table->index(['replayID', 'region', 'game_date'], 'replayID_Region_GameDate');
            $table->index(['replayID', 'tournament', 'season', 'division_0', 'division_1', 'round', 'game', 'game_date'], 'replayID_season_division_round');
            $table->index(['tournament', 'season', 'division_0', 'division_1', 'round'], 'season_division_round');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ngs.replay');
    }
}
