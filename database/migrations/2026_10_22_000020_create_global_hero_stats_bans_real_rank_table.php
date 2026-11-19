<?php

use Illuminate\Database\Migrations\Migration;
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
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_stats_bans_real_rank` (
                `global_hero_stats_bans_real_rank_id` bigint NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `real_league_tier` tinyint NOT NULL,
                `game_map` tinyint NOT NULL,
                `region` int NOT NULL,
                `hero` tinyint NOT NULL,
                `bans` double NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_stats_bans_real_rank_id`, `game_version`),
                KEY `Index_Bans` (`game_version`, `real_league_tier`, `game_map`, `region`, `hero`, `bans`),
                UNIQUE KEY `unique` (`game_version`, `real_league_tier`, `game_map`, `region`, `hero`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);

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
