<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeasonGameVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.season_game_versions', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('season');
          $table->string('game_version', 45);
          $table->dateTime('date_added');

          $table->primary(['season', 'game_version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.season_game_versions');
    }
}
