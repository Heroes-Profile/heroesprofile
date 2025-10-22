<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroTalentsWithHeroesTable extends Migration
{
    public function up()
    {
        Schema::create('global_hero_talents_with_heroes', function (Blueprint $table) {
            $table->increments('global_hero_talents_with_heroes_id');
            $table->integer('game_version');
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('game_map');
            $table->tinyInteger('hero');
            $table->tinyInteger('win_loss');
            $table->integer('level')->nullable();
            $table->integer('talent')->nullable();
            $table->tinyInteger('ally')->nullable();
            $table->unsignedInteger('games_played')->default(0);

            $table->unique(['game_version', 'game_type', 'league_tier', 'game_map', 'hero', 'win_loss', 'level', 'talent', 'ally'], 'unique');
            $table->index(['game_version', 'game_type', 'league_tier', 'game_map', 'hero', 'win_loss', 'level', 'talent', 'ally', 'games_played'], 'primary_gamesPlayed');
        });
    }

    public function down()
    {
        Schema::dropIfExists('global_hero_talents_with_heroes');
    }
}
