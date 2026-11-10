<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.player', function (Blueprint $table) {
            $table->unsignedInteger('replayID');
            $table->unsignedInteger('blizz_id');
            $table->string('battletag', 50);
            $table->unsignedTinyInteger('hero');
            $table->unsignedSmallInteger('hero_level');
            $table->integer('mastery_tier')->nullable();
            $table->integer('team_id')->nullable();
            $table->tinyInteger('team')->nullable();
            $table->unsignedTinyInteger('winner');

            $table->primary(['replayID', 'battletag', 'hero']);
            $table->index(['replayID', 'blizz_id', 'hero'], 'replayID_blizzID_Hero');
            $table->index(['blizz_id', 'hero'], 'blizzID_hero');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.player');
    }
}
