<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalCompositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_compositions` (
                `global_compositions_id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `game_version` int NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `hero_league_tier` tinyint NOT NULL DEFAULT 0,
                `role_league_tier` tinyint NOT NULL DEFAULT 0,
                `game_map` tinyint NOT NULL,
                `hero_level` int unsigned NOT NULL,
                `composition_id` int NOT NULL,
                `hero` tinyint NULL,
                `mirror` tinyint NOT NULL DEFAULT 0,
                `region` int NOT NULL,
                `win_loss` tinyint NOT NULL,
                `games_played` int unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`global_compositions_id`, `game_version`),
                KEY `index` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `composition_id`, `hero`, `mirror`, `region`, `win_loss`, `games_played`),
                UNIQUE KEY `UNIQUE` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `composition_id`, `hero`, `mirror`, `region`, `win_loss`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_compositions');
    }
}
