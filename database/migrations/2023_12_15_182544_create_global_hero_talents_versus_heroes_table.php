<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroTalentsVersusHeroesTable extends Migration
{
    public function up()
    {
        Schema::create('global_hero_talents_versus_heroes', function (Blueprint $table) {
            $table->id('global_hero_talents_versus_heroes_id');
            $table->integer('game_version');
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('game_map');
            $table->tinyInteger('hero');
            $table->tinyInteger('win_loss');
            $table->integer('level')->nullable();
            $table->integer('talent')->nullable();
            $table->integer('enemy')->nullable();
            $table->unsignedBigInteger('games_played')->default(0);

            $table->unique(['game_version', 'game_type', 'league_tier', 'game_map', 'hero', 'win_loss', 'level', 'talent', 'enemy'], 'unique');
            $table->index(['game_version', 'game_type', 'league_tier', 'game_map', 'hero', 'win_loss', 'level', 'talent', 'enemy', 'games_played'], 'primary_gamesPlayed');
        });
    }

    public function down()
    {
        Schema::dropIfExists('global_hero_talents_versus_heroes');
    }
}
