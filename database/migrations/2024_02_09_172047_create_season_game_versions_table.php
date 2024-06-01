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
            $table->id();
            $table->unsignedInteger('season');
            $table->string('game_version', 45);
            $table->dateTime('date_added')->nullable();
            $table->tinyInteger('updated_globals')->default(0);

            // Indexes
            $table->unique(['season', 'game_version'], 'unique');
            $table->index('updated_globals', 'globals');
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
