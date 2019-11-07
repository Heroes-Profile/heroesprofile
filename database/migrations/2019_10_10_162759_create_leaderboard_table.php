<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaderboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.leaderboard', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('game_type');
          $table->integer('type');
          $table->integer('rank');
          $table->string('split_battletag', 255);
          $table->string('battletag', 255);
          $table->integer('blizz_id');
          $table->tinyInteger('region');
          $table->double('win_rate');
          $table->integer('win');
          $table->integer('loss');
          $table->integer('games_played');
          $table->double('conservative_rating');
          $table->double('rating');
          $table->integer('cache_number');
          $table->primary(['game_type', 'type', 'rank', 'cache_number'], "Primary_Index");
          $table->index(['game_type', 'type', 'cache_number'], "Index 1");
          $table->index(['game_type', 'type', 'cache_number', 'region'], "Index 2");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_cache.leaderboard');
    }
}
