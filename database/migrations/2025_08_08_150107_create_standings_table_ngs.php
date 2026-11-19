<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandingsTableNgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ngs.standings', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('standings_id', true);
            $table->integer('season')->nullable();
            $table->string('division')->nullable();
            $table->integer('rank')->nullable();
            $table->integer('team_id')->nullable();
            $table->string('team_name')->nullable();
            $table->integer('points')->nullable();
            $table->integer('dominations')->nullable();
            $table->integer('games_won')->nullable();
            $table->integer('games_lost')->nullable();
            $table->integer('matches_played')->nullable();

            $table->unique(['season', 'division', 'rank'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ngs.standings');
    }
}
