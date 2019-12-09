<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.player', function (Blueprint $table) {
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
          $table->double('player_conservative_rating');
          $table->double('player_mean');
          $table->double('player_standard_deviation');
          $table->double('hero_conservative_rating');
          $table->double('hero_mean');
          $table->double('hero_standard_deviation');
          $table->double('role_conservative_rating');
          $table->double('role_mean');
          $table->double('role_standard_deviation');
          $table->dateTime('mmr_date_parsed');
          
          $table->primary(['replayID', 'battletag', 'hero'], 'Primary_Index');
          $table->index(['replayID', 'blizz_id', 'hero'], 'Index_1');
          $table->index(['blizz_id', 'hero'], 'Index_2');
          $table->index(['replayID', 'hero', 'player_conservative_rating'], 'Index_3');
          $table->index(['hero', 'player_conservative_rating'], 'Index_4');
          $table->index(['replayID', 'blizz_id', 'mmr_date_parsed'], 'Index_5');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.player');
    }
}
