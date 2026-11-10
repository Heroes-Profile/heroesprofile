<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.teams', function (Blueprint $table) {
            $table->increments('team_id');
            $table->string('tournament', 255)->nullable();
            $table->integer('season');
            $table->string('team_name', 200);
            $table->string('image', 200)->nullable()->default('no-image.png');

            $table->unique(['tournament', 'season', 'team_name'], 'Unique');
            $table->index('team_name');
            $table->index(['team_id', 'tournament', 'season', 'team_name'], 'teamID_season_division_');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.teams');
    }
}
