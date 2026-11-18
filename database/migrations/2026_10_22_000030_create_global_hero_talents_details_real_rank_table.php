<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * global_hero_talents_details for Storm League by real rank.
 * real_league_tier is the player's real Storm League rank at that game, read by the uploader: 1 bronze to 6 master,
 * 7 grandmaster.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_talents_details_real_rank` (
                `global_hero_talents_details_real_rank_id` bigint NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `real_league_tier` tinyint NOT NULL,
                `game_map` tinyint NOT NULL,
                `hero` tinyint NOT NULL,
                `mirror` tinyint NOT NULL DEFAULT 0,
                `region` int NOT NULL,
                `win_loss` tinyint NOT NULL,
                `level` int NOT NULL,
                `talent` int NOT NULL,
                `game_time` int unsigned NULL,
                `kills` int unsigned NULL,
                `assists` int unsigned NULL,
                `takedowns` int unsigned NULL,
                `deaths` int unsigned NULL,
                `highest_kill_streak` int unsigned NULL,
                `hero_damage` int unsigned NULL,
                `siege_damage` int unsigned NULL,
                `structure_damage` int unsigned NULL,
                `minion_damage` int unsigned NULL,
                `creep_damage` int unsigned NULL,
                `summon_damage` int unsigned NULL,
                `time_cc_enemy_heroes` int unsigned NULL,
                `healing` int unsigned NULL,
                `self_healing` int unsigned NULL,
                `damage_taken` int unsigned NULL,
                `experience_contribution` int unsigned NULL,
                `town_kills` int unsigned NULL,
                `time_spent_dead` int unsigned NULL,
                `merc_camp_captures` int unsigned NULL,
                `watch_tower_captures` int unsigned NULL,
                `protection_allies` int unsigned NULL,
                `silencing_enemies` int unsigned NULL,
                `rooting_enemies` int unsigned NULL,
                `stunning_enemies` int unsigned NULL,
                `clutch_heals` int unsigned NULL,
                `escapes` int unsigned NULL,
                `vengeance` int unsigned NULL,
                `outnumbered_deaths` int unsigned NULL,
                `teamfight_escapes` int unsigned NULL,
                `teamfight_healing` int unsigned NULL,
                `teamfight_damage_taken` int unsigned NULL,
                `teamfight_hero_damage` int unsigned NULL,
                `multikill` int unsigned NULL,
                `physical_damage` int unsigned NULL,
                `spell_damage` int unsigned NULL,
                `regen_globes` int NULL,
                `games_played` int unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_talents_details_real_rank_id`, `game_version`),
                KEY `primary_withHero` (`game_version`, `hero`, `real_league_tier`, `game_map`, `mirror`, `region`, `win_loss`, `level`, `talent`, `games_played`),
                UNIQUE KEY `unique` (`game_version`, `real_league_tier`, `game_map`, `hero`, `mirror`, `region`, `win_loss`, `level`, `talent`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);

        // Partitioned by game_version like the other global tables; BackendServerApplication's AddNewPartition
        // splits a new p_<id> off p_future for each new patch
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_talents_details_real_rank` MODIFY `global_hero_talents_details_real_rank_id` BIGINT NOT NULL AUTO_INCREMENT');
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_talents_details_real_rank` PARTITION BY RANGE (`game_version`) (PARTITION p_270 VALUES LESS THAN (271), PARTITION p_future VALUES LESS THAN MAXVALUE)');
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_talents_details_real_rank');
    }
};
