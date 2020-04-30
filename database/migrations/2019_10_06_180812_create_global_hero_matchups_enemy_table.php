<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalHeroMatchupsEnemyTable extends Migration
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
        $this->schema = Schema::connection(config('database.default'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('global_hero_matchups_enemy', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->increments('global_hero_matchups_enemy_id');
          $table->string('game_version', 45);
          $table->tinyInteger('game_type');
          $table->tinyInteger('league_tier');
          $table->tinyInteger('hero_league_tier');
          $table->tinyInteger('role_league_tier');
          $table->tinyInteger('game_map');
          $table->integer('hero_level')->unsigned()->default(0);
          $table->tinyInteger('hero');
          $table->tinyInteger('enemy');
          $table->tinyInteger('mirror');
          $table->integer('region');
          $table->tinyInteger('win_loss');
          $table->integer('games_played');

          $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'hero', 'enemy', 'mirror', 'region', 'win_loss'], 'global_hero_matchups_enemy_Unique');
          $table->index(['game_version', 'game_type', 'hero', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'enemy', 'mirror', 'region', 'win_loss', 'games_played'], 'global_hero_matchups_enemy_Base_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('global_hero_matchups_enemy');
    }
}
