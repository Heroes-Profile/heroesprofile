<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroDraftOrderTable extends Migration
{
    public function up()
    {
        DB::connection('heroesprofile_globals')->statement(<<<'SQL'
            CREATE TABLE `global_hero_draft_order` (
                `global_hero_draft_order_id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `game_version` int unsigned NOT NULL,
                `game_type` tinyint NOT NULL,
                `league_tier` tinyint NOT NULL,
                `hero_league_tier` tinyint NOT NULL DEFAULT 0,
                `role_league_tier` tinyint NOT NULL DEFAULT 0,
                `game_map` tinyint NOT NULL,
                `hero_level` int NOT NULL,
                `region` int NOT NULL,
                `hero` tinyint NOT NULL,
                `pick_number` int NULL,
                `win_loss` tinyint NULL,
                `count` double unsigned NULL DEFAULT 0,
                PRIMARY KEY (`global_hero_draft_order_id`, `game_version`),
                KEY `Index_Count` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `region`, `hero`, `pick_number`, `win_loss`, `count`),
                UNIQUE KEY `UNIQUE` (`game_version`, `game_type`, `league_tier`, `hero_league_tier`, `role_league_tier`, `game_map`, `hero_level`, `region`, `hero`, `pick_number`, `win_loss`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            PARTITION BY RANGE (`game_version`) (PARTITION pmax VALUES LESS THAN MAXVALUE)
            SQL);
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_draft_order');
    }
}
