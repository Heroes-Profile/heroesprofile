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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('id', true);
            $table->integer('season');
            $table->string('game_version', 45);
            $table->integer('major')->storedAs('cast(substring_index(`game_version`,_utf8mb4\'.\',1) as unsigned)')->nullable();
            $table->integer('minor')->storedAs('cast(substring_index(substring_index(`game_version`,_utf8mb4\'.\',-(3)),_utf8mb4\'.\',1) as unsigned)')->nullable();
            $table->integer('patch')->storedAs('cast(substring_index(substring_index(`game_version`,_utf8mb4\'.\',-(2)),_utf8mb4\'.\',1) as unsigned)')->nullable();
            $table->integer('build')->storedAs('cast(substring_index(`game_version`,_utf8mb4\'.\',-(1)) as unsigned)')->nullable();
            $table->dateTime('date_added')->nullable();
            $table->tinyInteger('valid_globals')->nullable()->default(1);
            $table->mediumText('patch_notes_url')->nullable();

            $table->index('valid_globals', 'globals');
            $table->index(['season', 'game_version'], 'unique');
            $table->unique('game_version', 'uq_season_game_versions_game_version');
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
