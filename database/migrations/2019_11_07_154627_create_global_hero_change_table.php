<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalHeroChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('heroesprofile_cache.global_hero_change', function (Blueprint $table) {
        $table->engine = 'InnoDB';
        $table->string('game_version', 45);
        $table->tinyInteger('game_type');
        $table->tinyInteger('hero');
        $table->double('win_rate');
        $table->double('popularity');
        $table->double('ban_rate');
        $table->integer('games_played');
        $table->integer('wins');
        $table->integer('losses');
        $table->integer('bans');

        $table->primary(['game_version', 'game_type', 'hero'], 'Primary_Index');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_cache.global_hero_change');
    }
}
