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
            $table->id('standings_id');
            $table->integer('season')->nullable();
            $table->string('division', 255)->nullable();
            $table->integer('rank')->nullable();
            $table->integer('team_id')->nullable();
            $table->string('team_name', 255)->nullable();
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
