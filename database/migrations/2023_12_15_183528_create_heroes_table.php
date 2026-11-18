<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateHeroesTable extends Migration
{
    public function up()
    {
        DB::connection('heroesprofile')->statement(<<<'SQL'
            CREATE TABLE `heroes` (
                `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `short_name` varchar(32) NOT NULL,
                `alt_name` varchar(45) NULL,
                `role` varchar(32) NULL,
                `new_role` varchar(45) NULL,
                `type` varchar(32) NULL,
                `release_date` datetime NULL,
                `rework_date` datetime NULL,
                `last_change_patch_version` varchar(25) NULL,
                `attribute_id` char(4) NULL,
                `build_copy_name` varchar(45) NULL,
                `last_updated` datetime NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `Hero` (`name`),
                KEY `heroes_name_index` (`name`(191)),
                KEY `heroes_shortcut_index` (`attribute_id`)
            ) ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
    }

    public function down()
    {
        Schema::dropIfExists('heroes');
    }
}
