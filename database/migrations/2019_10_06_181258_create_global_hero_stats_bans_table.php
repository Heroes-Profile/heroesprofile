<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalHeroStatsBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.global_hero_stats_bans', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('game_version', 45);
          $table->tinyInteger('game_type');
          $table->tinyInteger('league_tier');
          $table->tinyInteger('hero_league_tier');
          $table->tinyInteger('role_league_tier');
          $table->tinyInteger('game_map');
          $table->integer('hero_level')->unsigned();
          $table->integer('region');
          $table->tinyInteger('hero');
          $table->integer('bans')->unsigned()->default(0);

          $table->primary(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.global_hero_stats_bans');
    }
}
