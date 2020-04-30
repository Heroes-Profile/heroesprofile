<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalHeroStatsBansTable extends Migration
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
        $this->schema->create('global_hero_stats_bans', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('global_hero_stats_bans_id')->autoIncrement();
          $table->string('game_version', 45);
          $table->tinyInteger('game_type');
          $table->tinyInteger('league_tier');
          $table->tinyInteger('hero_league_tier');
          $table->tinyInteger('role_league_tier');
          $table->tinyInteger('game_map');
          $table->integer('hero_level')->unsigned();
          $table->integer('region');
          $table->tinyInteger('hero');
          $table->integer('bans')->unsigned()->default(0);

          $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero'], 'global_hero_stats_bans_Base_Unique');
          $table->index(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero', 'bans'], 'global_hero_stats_bans_Base_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('global_hero_stats_bans');
    }
}
