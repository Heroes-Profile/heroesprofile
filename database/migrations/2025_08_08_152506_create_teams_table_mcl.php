<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTableMcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_mcl.teams', function (Blueprint $table) {
            $table->increments('team_id');
            $table->integer('season');
            $table->string('team_name', 200);
            $table->string('image', 200)->default('no-image.png');

            $table->unique(['season', 'team_name'], 'Unique');
            $table->index(['team_id', 'season', 'team_name'], 'teamID_season_division_');
            $table->index('team_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_mcl.teams');
    }
}
