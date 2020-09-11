<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplayExperienceBreakdown extends Migration
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
    $this->schema->create('replay_experience_breakdown', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('replay_experience_breakdown_id')->autoIncrement();
      $table->integer('replayID');
      $table->tinyInteger('team');
      $table->integer('team_level');
      $table->string('timestamp', 500);
      $table->double('structureXP');
      $table->double('creepXP');
      $table->double('heroXP');
      $table->double('minionXP');
      $table->double('trickXP');
      $table->double('totalXP');

    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('replay_experience_breakdown');
  }
}
