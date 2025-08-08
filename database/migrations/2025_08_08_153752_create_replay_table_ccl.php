<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayTableCcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ccl.replay', function (Blueprint $table) {
            $table->increments('replayID');
            $table->string('season', 45)->nullable();
            $table->integer('type')->nullable();
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
            $table->unsignedTinyInteger('region');
            $table->dateTime('date_added');
            $table->tinyInteger('mmr_ran')->default(0);

            $table->index(['replayID', 'region', 'game_date'], 'replayID_Region_GameDate');
            $table->index(['replayID', 'season', 'round', 'game', 'game_date'], 'replayID_season_division_round');
            $table->index('region');
            $table->index('game_date');
            $table->index(['season', 'round'], 'season_division_round');
            $table->index(['season', 'team_0_id', 'team_1_id', 'round'], 'index');
            $table->index(['mmr_ran', 'game_date'], 'mmr_ran');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ccl.replay');
    }
}
