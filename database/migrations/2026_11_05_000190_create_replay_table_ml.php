<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.replay', function (Blueprint $table) {
            $table->increments('replayID');
            $table->string('series', 45)->nullable();
            $table->unsignedTinyInteger('region');
            $table->string('tournament', 255)->nullable();
            $table->string('season', 45)->nullable();
            $table->string('team_0_name', 200)->nullable();
            $table->string('team_1_name', 200)->nullable();
            $table->integer('round')->nullable();
            $table->integer('game')->nullable();
            $table->tinyInteger('first_pick')->nullable();
            $table->dateTime('game_date');
            $table->unsignedSmallInteger('game_length');
            $table->unsignedTinyInteger('game_map');
            $table->string('game_version', 32);
            $table->dateTime('date_added');

            $table->index(['series', 'team_0_name'], 'index1');
            $table->index(['series', 'team_1_name'], 'index2');
            $table->index(['series', 'season'], 'index3');
            $table->index(['series', 'region'], 'index4');
            $table->index(['series', 'tournament'], 'index5');
            $table->index(['series', 'region', 'tournament', 'season'], 'index6');
            $table->index(['series', 'region', 'tournament', 'season', 'game_map'], 'index7');
            $table->index(['series', 'region', 'tournament', 'season', 'team_0_name', 'team_1_name', 'game_map'], 'index8');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.replay');
    }
}
