<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayTableCCL extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ccl.replay', function (Blueprint $table) {
            $table->integer('replayID');
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
            
            $table->primary('replayID');
            $table->index(['replayID', 'region', 'game_date'], 'replayID_Region_GameDate');
            $table->index(['replayID', 'season', 'round', 'game', 'game_date'], 'replayID_season_division_round');
            $table->index('region');
            $table->index('game_date');
            $table->index(['season', 'round']);
            $table->index(['season', 'team_0_id', 'team_1_id', 'round']);
            $table->index(['mmr_ran', 'game_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replay_ccl');
    }
}
