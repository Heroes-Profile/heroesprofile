<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MmrTypeIds extends Migration
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
    $this->schema->create('mmr_type_ids', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('mmr_type_table_id')->autoIncrement();
      $table->integer('mmr_type_id')->unqiue();
      $table->string('name', 45);
      $table->primary('mmr_type_table_id');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('mmr_type_ids');
  }
}
