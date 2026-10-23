<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * global_hero_stats_bans for Storm League by real rank. Each ranked player adds 0.1 of a ban in their own tier.
 * real_league_tier is the player's real Storm League rank at that game, read by the uploader: 1 bronze to 6 master,
 * 7 grandmaster.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('heroesprofile_globals')->create('global_hero_stats_bans_real_rank', function (Blueprint $table) {
            $table->increments('global_hero_stats_bans_real_rank_id');
            $table->integer('game_version');
            $table->tinyInteger('real_league_tier');
            $table->tinyInteger('game_map');
            $table->integer('region');
            $table->tinyInteger('hero');
            $table->double('bans')->default(0);

            $table->unique(['game_version', 'real_league_tier', 'game_map', 'region', 'hero'], 'unique');
            $table->index(['game_version', 'real_league_tier', 'game_map', 'region', 'hero', 'bans'], 'Index_Bans');
        });
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_stats_bans_real_rank');
    }
};
