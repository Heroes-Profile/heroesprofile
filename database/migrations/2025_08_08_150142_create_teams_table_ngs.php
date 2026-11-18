<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTableNgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ngs.teams', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('team_id');
            $table->string('tournament', 45)->nullable()->default('NGS');
            $table->integer('season');
            $table->string('division', 45)->nullable();
            $table->string('team_name', 200);
            $table->string('image', 200)->nullable()->default('no-image.png');

            $table->primary('team_id');
            $table->index('team_name', 'team_name');
            $table->index(['team_id', 'tournament', 'season', 'division', 'team_name'], 'teamID_season_division_');
            $table->unique(['tournament', 'season', 'division', 'team_name'], 'Unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ngs.teams');
    }
}
