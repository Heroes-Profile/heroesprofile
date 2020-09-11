<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Maps extends Migration
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
    $this->schema->create('maps', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('map_id')->autoIncrement();
      $table->string('name', 255)->unqiue();
      $table->string('short_name', 255);
      $table->string('type', 255);
      $table->tinyInteger('ranked_rotation');
      $table->tinyInteger('playable');

    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('maps');
  }
}
