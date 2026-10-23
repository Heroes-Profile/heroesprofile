<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->bigInteger('global_hero_stats_bans_real_rank_id');
            $table->integer('game_version');
            $table->tinyInteger('real_league_tier');
            $table->tinyInteger('game_map');
            $table->integer('region');
            $table->tinyInteger('hero');
            $table->double('bans')->default(0);

            $table->primary(['global_hero_stats_bans_real_rank_id', 'game_version']);
            $table->unique(['game_version', 'real_league_tier', 'game_map', 'region', 'hero'], 'unique');
            $table->index(['game_version', 'real_league_tier', 'game_map', 'region', 'hero', 'bans'], 'Index_Bans');
        });

        // Partitioned by game_version like the other global tables; BackendServerApplication's AddNewPartition
        // splits a new p_<id> off p_future for each new patch
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_stats_bans_real_rank` MODIFY `global_hero_stats_bans_real_rank_id` BIGINT NOT NULL AUTO_INCREMENT');
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_stats_bans_real_rank` PARTITION BY RANGE (`game_version`) (PARTITION p_270 VALUES LESS THAN (271), PARTITION p_future VALUES LESS THAN MAXVALUE)');
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_stats_bans_real_rank');
    }
};
