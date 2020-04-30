<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplayTable extends Migration
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
        $this->schema->create('replay', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->tinyInteger('game_type');
          $table->dateTime('game_date');
          $table->smallInteger('game_length');
          $table->tinyInteger('game_map');
          $table->string('game_version', 32);
          $table->tinyInteger('region');
          $table->dateTime('date_added');
          $table->tinyInteger('mmr_ran');
          $table->tinyInteger('globals_ran');
          $table->double('player_match_quality');
          $table->double('hero_match_quality');
          $table->double('role_match_quality');

          $table->primary('replayID');
          $table->index(['replayID', 'region', 'game_date']);
          $table->index('region');
          $table->index('game_date');
          $table->index('mmr_ran');
          $table->index('globals_ran');
          $table->index(['region', 'game_type']);
          $table->index(['replayID', 'game_type', 'game_date']);
          $table->index(['replayID', 'region', 'game_type', 'game_date']);

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('replay');
    }
}
