<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Heroes extends Migration
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
    $this->schema->create('heroes', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('id')->autoIncrement()->unsigned();
      $table->string('name', 255);
      $table->string('short_name', 32);
      $table->string('alt_name', 45)->nullable();
      $table->string('role', 32)->nullable();
      $table->string('new_role', 45)->nullable();
      $table->string('type', 32)->nullable();
      $table->dateTime('release_date')->nullable();
      $table->dateTime('rework_date')->nullable();
      $table->char('attribute_id')->nullable();
      $table->string('build_copy_name', 45)->nullable();

      $table->primary('id');
      $table->unique('name');
      $table->index('attribute_id');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('heroes');
  }
}
