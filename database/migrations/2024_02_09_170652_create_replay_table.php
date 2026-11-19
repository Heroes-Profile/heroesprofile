<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replay', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->unsignedInteger('replayID');
            $table->tinyInteger('game_type');
            $table->dateTime('game_date');
            $table->unsignedSmallInteger('game_length');
            $table->unsignedTinyInteger('game_map');
            $table->string('game_version', 32);
            $table->unsignedTinyInteger('region');
            $table->dateTime('date_added');
            $table->tinyInteger('mmr_ran')->nullable()->default(0);
            $table->tinyInteger('globals_ran')->nullable()->default(0);
            $table->double('player_match_quality')->nullable();
            $table->double('hero_match_quality')->nullable();
            $table->double('role_match_quality')->nullable();
            $table->double('map_match_quality')->nullable();

            $table->primary('replayID');
            $table->index(['replayID', 'region', 'game_type', 'game_map', 'game_date'], '1');
            $table->index('date_added', 'dateAdded');
            $table->index('game_date', 'game_date');
            $table->index(['game_type', 'game_date'], 'gameType_gameDate');
            $table->index('game_version', 'gameversion');
            $table->index(['game_version', 'game_type', 'region', 'game_date'], 'gameVersionGameTypeRegionGameDate');
            $table->index(['globals_ran', 'game_date'], 'globals_ran');
            $table->index(['region', 'game_type', 'game_map', 'game_length'], 'idx_region_gameType_gameMap_length');
            $table->index(['game_type', 'replayID'], 'idx_replay_gameType_replayID');
            $table->index(['mmr_ran', 'game_date'], 'mmr_ran_gameDate');
            $table->index('region', 'region');
            $table->index(['region', 'game_type', 'game_date'], 'region_gameType');
            $table->index(['replayID', 'game_type', 'game_date'], 'replayID_gameType_gameDate');
            $table->index(['replayID', 'game_type', 'game_version'], 'replayID_gameType_version');
            $table->index(['replayID', 'game_version'], 'replayID_gameVersion');
            $table->index(['replayID', 'region', 'game_date'], 'replayID_Region_GameDate');
            $table->index(['replayID', 'region', 'game_type', 'game_date'], 'replayID_Region_GameType');
            $table->index(['game_type', 'game_version', 'game_map', 'region'], 'Replays');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replay');
    }
}
