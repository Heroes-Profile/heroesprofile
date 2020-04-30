<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrawlScoresTable extends Migration
{

    /**
     * The database schema.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection(config('database.brawl'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('scores', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->string('battletag', 50);
          $table->integer('level');
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
          $table->integer('meta_experience');
          $table->integer('match_award');
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
          $table->integer('first_to_ten');

          $table->primary(['replayID', 'battletag'], 'scores_Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('scores');
    }
}
