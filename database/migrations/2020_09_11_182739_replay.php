<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Replay extends Migration
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
      $table->tinyInteger('game_type')->nullable();
      $table->dateTime('game_date')->nullable();
      $table->smallInteger('game_length')->nullable();
      $table->tinyInteger('game_map')->nullable();
      $table->string('game_version', 32)->nullable();
      $table->tinyInteger('region')->nullable();
      $table->dateTime('date_added')->nullable();
      $table->tinyInteger('mmr_ran')->default(0)->nullable();
      $table->tinyInteger('globals_ran')->default(0)->nullable();
      $table->double('player_match_quality')->nullable();
      $table->double('hero_match_quality')->nullable();
      $table->double('role_match_quality')->nullable();

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
