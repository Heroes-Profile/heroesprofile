<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroStatsBansTable extends Migration
{
    public function up()
    {
        Schema::create('global_hero_stats_bans', function (Blueprint $table) {
            $table->increments('global_hero_stats_bans_id');
            $table->string('game_version', 45);
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('hero_league_tier')->default(0);
            $table->tinyInteger('role_league_tier')->default(0);
            $table->tinyInteger('game_map');
            $table->integer('hero_level');
            $table->integer('region');
            $table->tinyInteger('hero');
            $table->double('bans')->default(0);

            $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero'], 'unique');
            $table->index(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero', 'bans'], 'Index_Bans');
        });
    }

    public function down()
    {
        Schema::dropIfExists('global_hero_stats_bans');
    }
}
