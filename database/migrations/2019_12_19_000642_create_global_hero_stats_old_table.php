<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroStatsOldTable extends Migration
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
        $this->schema->create('global_hero_stats_old', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('game_version', 45);
          $table->tinyInteger('game_type');
          $table->tinyInteger('hero');
          $table->tinyInteger('win_loss');
          $table->integer('games_played');

          $table->primary(['game_version', 'game_type', 'hero', 'win_loss'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('global_hero_stats_old');
    }
}
