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
            $table->increments('id');
            $table->integer('season');
            $table->string('game_version', 45);
            $table->dateTime('date_added')->nullable();
            $table->tinyInteger('valid_globals')->default(1);
            $table->mediumText('patch_notes_url')->nullable();
        });

        // Generated (stored) columns are not directly supported in Laravel schema builder
        DB::statement("
            ALTER TABLE `season_game_versions`
            ADD COLUMN `major` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(`game_version`, '.', 1) AS UNSIGNED)) STORED,
            ADD COLUMN `minor` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`game_version`, '.', -3), '.', 1) AS UNSIGNED)) STORED,
            ADD COLUMN `patch` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`game_version`, '.', -2), '.', 1) AS UNSIGNED)) STORED,
            ADD COLUMN `build` int GENERATED ALWAYS AS (CAST(SUBSTRING_INDEX(`game_version`, '.', -1) AS UNSIGNED)) STORED
        ");

        Schema::table('season_game_versions', function (Blueprint $table) {
            $table->index(['season', 'game_version'], 'unique');
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
