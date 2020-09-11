<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeasonDates extends Migration
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
    $this->schema->create('season_dates', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('id')->autoIncrement();
      $table->integer('year');
      $table->double('season');
      $table->dateTime('start_date');
      $table->dateTime('end_date');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('season_dates');
  }
}
