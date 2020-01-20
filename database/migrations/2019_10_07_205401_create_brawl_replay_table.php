<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrawlReplayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_brawl.replay', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->dateTime('game_date');
          $table->smallInteger('game_length');
          $table->tinyInteger('game_map');
          $table->string('game_version', 32);
          $table->tinyInteger('region');
          $table->dateTime('date_added');
          $table->tinyInteger('globals_ran');

          $table->primary('replayID');
          $table->index(['replayID', 'region', 'game_date']);
          $table->index('region');
          $table->index('game_date');
          $table->index(['replayID', 'game_date']);
          $table->index('globals_ran');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_brawl.replay');
    }
}
