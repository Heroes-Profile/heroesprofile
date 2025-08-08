<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonGameVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('season_game_versions', function (Blueprint $table) {
            $table->increments('id'); // int NOT NULL AUTO_INCREMENT PRIMARY KEY
            $table->unsignedInteger('season');
            $table->string('game_version', 45);
            $table->dateTime('date_added')->nullable();

            // This replaces your tinyint updated_globals with valid_globals with default 1
            $table->tinyInteger('valid_globals')->default(1);

            // Generated (computed) columns are not directly supported in Laravel schema builder
            // So we need to add them via raw SQL after creating the table.
        });

        // Add generated columns using raw SQL after the table creation
        DB::statement("
            ALTER TABLE `season_game_versions`
            ADD COLUMN `major` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(`game_version`, '.', 1) AS UNSIGNED)) STORED,
            ADD COLUMN `minor` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`game_version`, '.', -3), '.', 1) AS UNSIGNED)) STORED,
            ADD COLUMN `patch` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`game_version`, '.', -2), '.', 1) AS UNSIGNED)) STORED,
            ADD COLUMN `build` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(`game_version`, '.', -1) AS UNSIGNED)) STORED
        ");

        // Add indexes
        Schema::table('season_game_versions', function (Blueprint $table) {
            $table->unique(['season', 'game_version'], 'unique');
            $table->index('valid_globals', 'globals');
            $table->index('game_version', 'gameversion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_game_versions');
    }
}
