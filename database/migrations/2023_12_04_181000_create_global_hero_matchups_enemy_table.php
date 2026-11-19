<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroMatchupsEnemyTable extends Migration
{
    public function up()
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_matchups_enemy` (
                `global_hero_matchups_enemy_id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `game_version` int unsigned NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `hero_league_tier` tinyint NOT NULL DEFAULT 0,
                `role_league_tier` tinyint NOT NULL DEFAULT 0,
                `game_map` tinyint NOT NULL,
                `hero_level` int unsigned NOT NULL DEFAULT 0,
                `hero` tinyint NOT NULL,
                `enemy` tinyint NOT NULL,
                `mirror` tinyint NOT NULL DEFAULT 0,
                `region` int NOT NULL,
                `win_loss` tinyint NOT NULL,
                `games_played` int unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_matchups_enemy_id`, `game_version`),
                KEY `primary_withGamesPlayed` (`game_version`, `game_type`, `hero`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `enemy`, `mirror`, `region`, `win_loss`, `games_played`),
                UNIQUE KEY `Unique` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `hero`, `enemy`, `mirror`, `region`, `win_loss`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_matchups_enemy');
    }
}
