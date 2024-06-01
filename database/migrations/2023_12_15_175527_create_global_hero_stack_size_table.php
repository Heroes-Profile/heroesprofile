<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroStackSizeTable extends Migration
{
    public function up()
    {
        Schema::create('global_hero_stack_size', function (Blueprint $table) {
            $table->id('global_hero_stats_id');
            $table->string('game_version', 45);
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('hero_league_tier')->default(0);
            $table->tinyInteger('role_league_tier')->default(0);
            $table->tinyInteger('game_map');
            $table->integer('hero_level');
            $table->tinyInteger('mirror')->default(0);
            $table->integer('region');
            $table->tinyInteger('win_loss');
            $table->tinyInteger('hero');
            $table->integer('hero_stack_size')->nullable();
            $table->string('team_ally_stack_value')->nullable();
            $table->string('team_enemy_stack_value')->nullable();
            $table->integer('games_played')->default(0);

            $table->unique([
                'game_version',
                'game_type',
                'league_tier',
                'hero_league_tier',
                'role_league_tier',
                'game_map',
                'hero_level',
                'mirror',
                'region',
                'win_loss',
                'hero',
                'hero_stack_size',
                'team_ally_stack_value',
                'team_enemy_stack_value'
            ], 'unique');

            $table->index([
                'game_version',
                'game_type',
                'league_tier',
                'hero_league_tier',
                'role_league_tier',
                'game_map',
                'hero_level',
                'mirror',
                'region',
                'win_loss',
                'team_ally_stack_value',
                'hero',
                'games_played'
            ], 'primary_gamesPlayed');
        });
    }

    public function down()
    {
        Schema::dropIfExists('global_hero_stack_size');
    }
}
