<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroTalentsDetailsTable extends Migration
{
    public function up()
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_talents_details` (
                `global_hero_talent_details_id` bigint NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `hero_league_tier` tinyint NOT NULL DEFAULT 0,
                `role_league_tier` tinyint NOT NULL DEFAULT 0,
                `game_map` tinyint NOT NULL,
                `hero_level` int unsigned NOT NULL,
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
                `games_played` int unsigned NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_talent_details_id`, `game_version`),
                KEY `primary_withHero` (`game_version`, `game_type`, `hero`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `mirror`, `region`, `win_loss`, `level`, `talent`, `games_played`),
                UNIQUE KEY `unique` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `hero`, `mirror`, `region`, `win_loss`, `level`, `talent`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_talents_details');
    }
}
