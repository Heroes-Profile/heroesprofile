<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Players' real Storm League ranks, read off the loading screen by the uploader's opt-in rank reader
 * (RealRankService). One row per rank period: a run of games a player was seen at the same rank. A new
 * row starts when the rank read changes, or a new season begins (by season_dates, not stored), so a
 * player's rows in date order are their progress. Linked to `battletags` by player_id; the replays a
 * period was first and last seen in by replayID.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battletags_real_ranks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('player_id');
            $table->unsignedBigInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            // The season_dates id the period's games are in; null for games no season covers yet
            $table->unsignedInteger('season')->nullable();

            // league_tiers: 1 bronze to 6 master. Null when unranked. A grandmaster is master (6) with
            // grandmaster set and their place on the ladder in ladder_position.
            $table->unsignedTinyInteger('league_tier')->nullable();
            $table->unsignedTinyInteger('division')->nullable();
            $table->boolean('grandmaster')->default(false);
            $table->unsignedSmallInteger('ladder_position')->nullable();
            $table->boolean('unranked')->default(false);

            $table->unsignedInteger('first_replayID');
            $table->dateTime('first_game_date');
            $table->unsignedInteger('last_replayID');
            $table->dateTime('last_game_date');
            $table->unsignedInteger('read_count')->default(1);

            // Who started the period: an HMAC of the sender's IP (as rank samples keep it), so everything
            // one bad source sent can be found and removed.
            $table->string('reporter', 64);
            $table->string('uploader_version', 45)->nullable();

            $table->index(['blizz_id', 'region', 'last_game_date'], 'player_history');
            $table->index('player_id');
            // The rank leaderboard reads one season's periods
            $table->index(['season', 'region'], 'season_region');
            $table->index('reporter');
        });

        DB::statement('ALTER TABLE `battletags_real_ranks` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        Schema::dropIfExists('battletags_real_ranks');
    }
};
