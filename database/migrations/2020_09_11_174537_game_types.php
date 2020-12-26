<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GameTypes extends Migration
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
    $this->schema->create('game_types', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('type_id');
      $table->string('name', 45);
      $table->string('short_name', 45);
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('game_types');
  }
}
