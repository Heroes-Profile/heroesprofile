<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroMatchupsEnemyTable extends Migration
{
    public function up()
    {
        Schema::connection('heroesprofile_globals')->create('global_hero_matchups_enemy', function (Blueprint $table) {
            $table->increments('global_hero_matchups_enemy_id');
            $table->integer('game_version')->notNull();
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('hero_league_tier')->default(0);
            $table->tinyInteger('role_league_tier')->default(0);
            $table->tinyInteger('game_map');
            $table->unsignedInteger('hero_level')->default(0);
            $table->tinyInteger('hero');
            $table->tinyInteger('enemy');
            $table->tinyInteger('mirror')->default(0);
            $table->integer('region');
            $table->tinyInteger('win_loss');
            $table->unsignedInteger('games_played')->default(0);

            $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'hero', 'enemy', 'mirror', 'region', 'win_loss'], 'Unique');
            $table->index(['game_version', 'game_type', 'hero', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'enemy', 'mirror', 'region', 'win_loss', 'games_played'], 'index_WithHero');

        });
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_matchups_enemy');
    }
}
