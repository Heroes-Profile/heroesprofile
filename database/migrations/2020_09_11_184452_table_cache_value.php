<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableCacheValue extends Migration
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
    $this->schema = Schema::connection(config('database.cache'));
  }

  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    $this->schema->create('table_cache_value', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('table_cache_value_id')->autoIncrement();
      $table->string('table_to_cache', 45);
      $table->integer('season');
      $table->integer('cache_number');
      $table->dateTime('date_cached');

      $table->unique(['table_to_cache', 'season', 'cache_number'], "table_cache_value_Base_Unique");
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('table_cache_value');
  }
}
