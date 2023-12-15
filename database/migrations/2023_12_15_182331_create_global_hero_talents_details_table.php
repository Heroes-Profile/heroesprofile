<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroTalentsDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('global_hero_talents_details', function (Blueprint $table) {
            $table->id('global_hero_talent_details_id');
            $table->string('game_version', 45)->nullable();
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('hero_league_tier')->default(0);
            $table->tinyInteger('role_league_tier')->default(0);
            $table->tinyInteger('game_map');
            $table->integer('hero_level')->unsigned();
            $table->tinyInteger('hero');
            $table->tinyInteger('mirror')->default(0);
            $table->integer('region');
            $table->tinyInteger('win_loss');
            $table->integer('level');
            $table->integer('talent');
            $table->integer('game_time')->unsigned()->nullable();
            $table->integer('kills')->unsigned()->nullable();
            $table->integer('assists')->unsigned()->nullable();
            $table->integer('takedowns')->unsigned()->nullable();
            $table->integer('deaths')->unsigned()->nullable();
            $table->integer('highest_kill_streak')->unsigned()->nullable();
            $table->integer('hero_damage')->unsigned()->nullable();
            $table->integer('siege_damage')->unsigned()->nullable();
            $table->integer('structure_damage')->unsigned()->nullable();
            $table->integer('minion_damage')->unsigned()->nullable();
            $table->integer('creep_damage')->unsigned()->nullable();
            $table->integer('summon_damage')->unsigned()->nullable();
            $table->integer('time_cc_enemy_heroes')->unsigned()->nullable();
            $table->integer('healing')->unsigned()->nullable();
            $table->integer('self_healing')->unsigned()->nullable();
            $table->integer('damage_taken')->unsigned()->nullable();
            $table->integer('experience_contribution')->unsigned()->nullable();
            $table->integer('town_kills')->unsigned()->nullable();
            $table->integer('time_spent_dead')->unsigned()->nullable();
            $table->integer('merc_camp_captures')->unsigned()->nullable();
            $table->integer('watch_tower_captures')->unsigned()->nullable();
            $table->integer('protection_allies')->unsigned()->nullable();
            $table->integer('silencing_enemies')->unsigned()->nullable();
            $table->integer('rooting_enemies')->unsigned()->nullable();
            $table->integer('stunning_enemies')->unsigned()->nullable();
            $table->integer('clutch_heals')->unsigned()->nullable();
            $table->integer('escapes')->unsigned()->nullable();
            $table->integer('vengeance')->unsigned()->nullable();
            $table->integer('outnumbered_deaths')->unsigned()->nullable();
            $table->integer('teamfight_escapes')->unsigned()->nullable();
            $table->integer('teamfight_healing')->unsigned()->nullable();
            $table->integer('teamfight_damage_taken')->unsigned()->nullable();
            $table->integer('teamfight_hero_damage')->unsigned()->nullable();
            $table->integer('multikill')->unsigned()->nullable();
            $table->integer('physical_damage')->unsigned()->nullable();
            $table->integer('spell_damage')->unsigned()->nullable();
            $table->integer('regen_globes')->nullable();
            $table->integer('games_played')->unsigned()->default(0);

            $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'hero', 'mirror', 'region', 'win_loss', 'level', 'talent'], 'unique');
            $table->index(['game_version', 'game_type', 'hero', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'mirror', 'region', 'win_loss', 'level', 'talent', 'games_played'], 'primary_withHero');
        });
    }

    public function down()
    {
        Schema::dropIfExists('global_hero_talents_details');
    }
}
