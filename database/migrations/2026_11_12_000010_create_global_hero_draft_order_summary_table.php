<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * global_hero_draft_order summed over hero_league_tier, role_league_tier, hero_level and win_loss, so the Drafter's
 * basic filters (timeframe, region, map, player rank) read far fewer rows. Each player adds 0.2 of a pick, as in the full table.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_draft_order_summary` (
                `global_hero_draft_order_summary_id` bigint NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `game_map` tinyint NOT NULL,
                `region` int NOT NULL,
                `hero` tinyint NOT NULL,
                `pick_number` int NULL,
                `count` double NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_draft_order_summary_id`, `game_version`),
                KEY `Index_Count` (`game_version`, `game_type`, `league_tier`, `game_map`, `region`, `hero`, `pick_number`, `count`),
                UNIQUE KEY `unique` (`game_version`, `game_type`, `league_tier`, `game_map`, `region`, `hero`, `pick_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);

        // Partitioned by game_version like the other global tables; BackendServerApplication's AddNewPartition
        // splits a new p_<id> off p_future for each new patch
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_draft_order_summary` MODIFY `global_hero_draft_order_summary_id` BIGINT NOT NULL AUTO_INCREMENT');
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_draft_order_summary` PARTITION BY RANGE (`game_version`) (PARTITION p_270 VALUES LESS THAN (271), PARTITION p_future VALUES LESS THAN MAXVALUE)');
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_draft_order_summary');
    }
};
