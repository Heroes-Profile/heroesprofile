<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoresTableMcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_mcl.scores', function (Blueprint $table) {
            $table->unsignedInteger('replayID');
            $table->string('battletag', 50);
            $table->integer('level')->nullable();
            $table->integer('kills')->nullable();
            $table->integer('assists')->nullable();
            $table->integer('takedowns')->nullable();
            $table->integer('deaths')->nullable();
            $table->integer('highest_kill_streak')->nullable();
            $table->integer('hero_damage')->nullable();
            $table->integer('siege_damage')->nullable();
            $table->integer('structure_damage')->nullable();
            $table->integer('minion_damage')->nullable();
            $table->integer('creep_damage')->nullable();
            $table->integer('summon_damage')->nullable();
            $table->integer('time_cc_enemy_heroes')->nullable();
            $table->integer('healing')->nullable();
            $table->integer('self_healing')->nullable();
            $table->integer('damage_taken')->nullable();
            $table->integer('experience_contribution')->nullable();
            $table->integer('town_kills')->nullable();
            $table->integer('time_spent_dead')->nullable();
            $table->integer('merc_camp_captures')->nullable();
            $table->integer('watch_tower_captures')->nullable();
            $table->integer('meta_experience')->nullable();
            $table->integer('protection_allies')->nullable();
            $table->integer('silencing_enemies')->nullable();
            $table->integer('rooting_enemies')->nullable();
            $table->integer('stunning_enemies')->nullable();
            $table->integer('clutch_heals')->nullable();
            $table->integer('escapes')->nullable();
            $table->integer('vengeance')->nullable();
            $table->integer('outnumbered_deaths')->nullable();
            $table->integer('teamfight_escapes')->nullable();
            $table->integer('teamfight_healing')->nullable();
            $table->integer('teamfight_damage_taken')->nullable();
            $table->integer('teamfight_hero_damage')->nullable();
            $table->integer('multikill')->nullable();
            $table->integer('physical_damage')->nullable();
            $table->integer('spell_damage')->nullable();
            $table->integer('spray')->nullable();
            $table->integer('regen_globes')->nullable();
            $table->tinyInteger('first_to_ten')->nullable();

            $table->primary(['replayID', 'battletag']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_mcl.scores');
    }
}
