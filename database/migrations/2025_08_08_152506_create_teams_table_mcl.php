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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('team_id', true);
            $table->integer('season');
            $table->string('team_name', 200);
            $table->string('image', 200)->nullable()->default('no-image.png');

            $table->index('team_name', 'team_name');
            $table->index(['team_id', 'season', 'team_name'], 'teamID_season_division_');
            $table->unique(['season', 'team_name'], 'Unique');
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
