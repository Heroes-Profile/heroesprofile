<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalHeroCompsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.global_hero_comps', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('game_version', 45);
          $table->tinyInteger('league_tier');
          $table->tinyInteger('game_type');
          $table->tinyInteger('game_map');
          $table->tinyInteger('hero');
          $table->integer('hero_level')->unsigned()->default(0);
          $table->integer('composition');
          $table->tinyInteger('win_loss');
          $table->integer('games_played');
          $table->primary(['game_version', 'league_tier', 'game_type', 'game_map', 'hero', 'hero_level', 'composition', 'win_loss'], 'Primary_Index');
          $table->index(['game_version', 'game_type', 'game_map', 'hero', 'hero_level', 'composition', 'win_loss'], 'Base_Index');
          $table->index(['game_version', 'game_type', 'hero', 'hero_level', 'composition', 'win_loss'], 'No_Game_Map');
          $table->index(['game_version', 'game_type', 'hero', 'composition', 'win_loss'], 'No_Game_Map_Hero_Level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_hero_comps');
    }
}
