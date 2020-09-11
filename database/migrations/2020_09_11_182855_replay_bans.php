<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplayBans extends Migration
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
    $this->schema->create('replay_bans', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('ban_id')->autoIncrement();
      $table->integer('replayID');
      $table->tinyInteger('team');
      $table->integer('hero');
      $table->index(['replayID', 'team', 'hero'], 'replay_bans_index');

    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('replay_bans');
  }
}
