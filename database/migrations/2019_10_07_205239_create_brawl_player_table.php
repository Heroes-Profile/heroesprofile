<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrawlPlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_brawl.player', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->integer('blizz_id');
          $table->string('battletag', 50);
          $table->tinyInteger('hero');
          $table->smallInteger('hero_level');
          $table->smallInteger('mastery_taunt');
          $table->tinyInteger('team');
          $table->tinyInteger('winner');
          $table->string('party', 45);
          $table->primary(['replayID', 'battletag', 'hero']);
          $table->index(['replayID', 'blizz_id', 'hero']);
          $table->index(['blizz_id', 'hero']);
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_brawl.player');
    }
}
