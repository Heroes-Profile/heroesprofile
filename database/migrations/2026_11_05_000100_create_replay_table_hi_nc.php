<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayTableHiNc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_hi_nc.replay', function (Blueprint $table) {
            $table->increments('replayID');
            $table->string('season', 45)->nullable();
            $table->string('type', 45)->nullable();
            $table->string('stage', 45)->nullable();
            $table->integer('region')->nullable();
            $table->string('team_0_id', 200)->nullable();
            $table->string('team_1_id', 200)->nullable();
            $table->integer('round')->nullable();
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
            $table->dateTime('date_added');

            $table->index(['replayID', 'region', 'game_date'], 'replayID_Region_GameDate');
            $table->index(['replayID', 'season', 'round', 'game', 'game_date'], 'replayID_season_division_round');
            $table->index('region');
            $table->index('game_date');
            $table->index(['season', 'round'], 'season_division_round');
            $table->index(['season', 'type', 'region', 'team_0_id', 'team_1_id', 'round'], 'index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_hi_nc.replay');
    }
}
