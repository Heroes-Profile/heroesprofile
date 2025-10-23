<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaderboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaderboard', function (Blueprint $table) {
            $table->increments('leaderboard_id');
            $table->integer('game_type');
            $table->integer('season');
            $table->integer('type');
            $table->integer('stack_size')->default(0);
            $table->integer('rank');
            $table->string('split_battletag', 255)->nullable();
            $table->string('battletag', 255);
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->double('win_rate')->nullable();
            $table->integer('win')->nullable();
            $table->integer('loss')->nullable();
            $table->integer('games_played')->nullable();
            $table->double('conservative_rating');
            $table->double('rating')->nullable();
            $table->double('normalized_rating')->nullable();
            $table->tinyInteger('most_played_hero')->nullable();
            $table->integer('level_one')->nullable();
            $table->integer('level_four')->nullable();
            $table->integer('level_seven')->nullable();
            $table->integer('level_ten')->nullable();
            $table->integer('level_thirteen')->nullable();
            $table->integer('level_sixteen')->nullable();
            $table->integer('level_twenty')->nullable();
            $table->integer('hero_build_games_played')->nullable();
            $table->timestamps();

            $table->unique(['game_type', 'season', 'type', 'stack_size', 'rank'], 'unique');
            $table->index(['game_type', 'season', 'type', 'stack_size'], 'gamrType_Season_type_cache');
            $table->index(['game_type', 'type', 'stack_size'], 'gameType_Type_Cache');
            $table->index(['type', 'stack_size'], 'Type_Cache');
            $table->index(['season', 'stack_size'], 'seaon_CacheNumber');
            $table->index(['game_type', 'season', 'type', 'stack_size', 'rank', 'region', 'conservative_rating'], 'type_cache_region');
            $table->index(['game_type', 'season', 'type', 'stack_size', 'games_played'], 'IndexWithGamesPlayed');
            $table->index(['season', 'win_rate'], 'seasonWinRate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaderboard');
    }
}
