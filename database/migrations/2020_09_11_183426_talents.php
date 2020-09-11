<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Talents extends Migration
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
    $this->schema->create('talents', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('replayID');
      $table->string('battletag', 50);
      $table->integer('level_one');
      $table->integer('level_four');
      $table->integer('level_seven');
      $table->integer('level_ten');
      $table->integer('level_thirteen');
      $table->integer('level_sixteen');
      $table->integer('level_twenty');

      $table->primary(['replayID', 'battletag']);
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('talents');
  }
}
