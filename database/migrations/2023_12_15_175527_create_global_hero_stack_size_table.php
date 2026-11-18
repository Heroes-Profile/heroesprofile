<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroStackSizeTable extends Migration
{
    public function up()
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_stack_size` (
                `global_hero_stack_size_id` bigint NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `hero_league_tier` tinyint NOT NULL DEFAULT 0,
                `role_league_tier` tinyint NOT NULL DEFAULT 0,
                `game_map` tinyint NOT NULL,
                `hero_level` int unsigned NOT NULL,
                `mirror` tinyint NOT NULL DEFAULT 0,
                `region` int NOT NULL,
                `win_loss` tinyint NOT NULL,
                `hero` tinyint NOT NULL,
                `hero_stack_size` int NULL,
                `team_ally_stack_value` varchar(5) NULL,
                `team_enemy_stack_value` varchar(5) NULL,
                `games_played` int unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_stack_size_id`, `game_version`),
                KEY `primary_gamesPlayed` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `mirror`, `region`, `win_loss`, `team_ally_stack_value`, `hero`, `games_played`),
                UNIQUE KEY `unique` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `mirror`, `region`, `win_loss`, `hero`, `hero_stack_size`, `team_ally_stack_value`, `team_enemy_stack_value`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_stack_size');
    }
}
