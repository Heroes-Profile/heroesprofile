<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalHeroStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.global_hero_stats', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('game_version', 45);
          $table->tinyInteger('game_type');
          $table->tinyInteger('league_tier');
          $table->tinyInteger('hero_league_tier');
          $table->tinyInteger('role_league_tier');
          $table->tinyInteger('game_map');
          $table->integer('hero_level')->unsigned();
          $table->tinyInteger('hero');
          $table->tinyInteger('mirror');
          $table->integer('region');
          $table->tinyInteger('win_loss');
          $table->integer('game_time');
          $table->integer('kills');
          $table->integer('assists');
          $table->integer('takedowns');
          $table->integer('deaths');
          $table->integer('highest_kill_streak');
          $table->integer('hero_damage');
          $table->integer('siege_damage');
          $table->integer('structure_damage');
          $table->integer('minion_damage');
          $table->integer('creep_damage');
          $table->integer('summon_damage');
          $table->integer('time_cc_enemy_heroes');
          $table->integer('healing');
          $table->integer('self_healing');
          $table->integer('damage_taken');
          $table->integer('experience_contribution');
          $table->integer('town_kills');
          $table->integer('time_spent_dead');
          $table->integer('merc_camp_captures');
          $table->integer('watch_tower_captures');
          $table->integer('protection_allies');
          $table->integer('silencing_enemies');
          $table->integer('rooting_enemies');
          $table->integer('stunning_enemies');
          $table->integer('clutch_heals');
          $table->integer('escapes');
          $table->integer('vengeance');
          $table->integer('outnumbered_deaths');
          $table->integer('teamfight_escapes');
          $table->integer('teamfight_healing');
          $table->integer('teamfight_damage_taken');
          $table->integer('teamfight_hero_damage');
          $table->integer('multikill');
          $table->integer('physical_damage');
          $table->integer('spell_damage');
          $table->integer('regen_globes');
          $table->integer('games_played');

          $table->primary(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'hero', 'mirror', 'region', 'win_loss'], 'Primary_Index');
          $table->index(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'hero', 'mirror', 'region', 'win_loss', 'games_played'], 'Base_Index');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.global_hero_stats');
    }
}
