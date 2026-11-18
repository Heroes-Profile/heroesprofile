<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroTalentsWithHeroesTable extends Migration
{
    public function up()
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_talents_with_heroes` (
                `global_hero_talents_with_heroes_id` bigint NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `game_map` tinyint NOT NULL,
                `hero` tinyint NOT NULL,
                `win_loss` tinyint NOT NULL,
                `level` int NULL,
                `talent` int NULL,
                `ally` tinyint NULL,
                `games_played` int unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_talents_with_heroes_id`, `game_version`),
                KEY `primary_gamesPlayed` (`game_version`, `game_type`, `league_tier`, `game_map`, `hero`, `win_loss`, `level`, `talent`, `ally`, `games_played`),
                UNIQUE KEY `unique` (`game_version`, `game_type`, `league_tier`, `game_map`, `hero`, `win_loss`, `level`, `talent`, `ally`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_talents_with_heroes');
    }
}
