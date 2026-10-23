<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * global_hero_talents_details for Storm League by real rank.
 * real_league_tier is the player's real Storm League rank at that game, read by the uploader: 1 bronze to 6 master,
 * 7 grandmaster.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('heroesprofile_globals')->create('global_hero_talents_details_real_rank', function (Blueprint $table) {
            $table->bigInteger('global_hero_talents_details_real_rank_id');
            $table->integer('game_version');
            $table->tinyInteger('real_league_tier');
            $table->tinyInteger('game_map');
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

            $table->primary(['global_hero_talents_details_real_rank_id', 'game_version']);
            $table->unique(['game_version', 'real_league_tier', 'game_map', 'hero', 'mirror', 'region', 'win_loss', 'level', 'talent'], 'unique');
            $table->index(['game_version', 'hero', 'real_league_tier', 'game_map', 'mirror', 'region', 'win_loss', 'level', 'talent', 'games_played'], 'primary_withHero');
        });

        // Partitioned by game_version like the other global tables; BackendServerApplication's AddNewPartition
        // splits a new p_<id> off p_future for each new patch
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_talents_details_real_rank` MODIFY `global_hero_talents_details_real_rank_id` BIGINT NOT NULL AUTO_INCREMENT');
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_talents_details_real_rank` PARTITION BY RANGE (`game_version`) (PARTITION p_270 VALUES LESS THAN (271), PARTITION p_future VALUES LESS THAN MAXVALUE)');
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_talents_details_real_rank');
    }
};
