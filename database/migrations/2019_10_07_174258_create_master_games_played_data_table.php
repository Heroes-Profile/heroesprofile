<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterGamesPlayedDataTable extends Migration
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
        $this->schema->create('master_games_played_data', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('type_value');
          $table->double('season');
          $table->tinyInteger('game_type');
          $table->integer('blizz_id');
          $table->tinyInteger('region');
          $table->integer('win');
          $table->integer('loss');
          $table->integer('games_played');

          $table->primary(['type_value', 'season', 'game_type', 'blizz_id', 'region'], 'Primary_Index');
          $table->index('games_played');
          $table->index(['season', 'game_type', 'games_played'], 'Index_1');
          $table->index(['type_value', 'season', 'game_type', 'region'], 'Index_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('master_games_played_data');
    }
}
