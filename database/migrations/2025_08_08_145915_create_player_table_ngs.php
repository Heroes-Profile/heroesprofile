<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerTableNgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ngs.player', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('player_id', true);
            $table->unsignedInteger('replayID');
            $table->unsignedInteger('blizz_id');
            $table->string('battletag', 50);
            $table->unsignedTinyInteger('hero');
            $table->unsignedSmallInteger('hero_level');
            $table->integer('mastery_tier')->nullable();
            $table->string('team_name', 200)->nullable();
            $table->unsignedTinyInteger('team');
            $table->unsignedTinyInteger('winner');
            $table->string('party', 45)->nullable();

            $table->index(['blizz_id', 'hero'], 'blizzID_hero');
            $table->index(['replayID', 'blizz_id', 'hero'], 'replayID_blizzID_Hero');
            $table->unique(['replayID', 'battletag', 'hero'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ngs.player');
    }
}
