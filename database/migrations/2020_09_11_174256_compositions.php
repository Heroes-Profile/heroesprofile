<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Compositions extends Migration
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
    $this->schema->create('compositions', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('composition_id')->autoIncrement();
      $table->integer('role_one')->nullable();
      $table->integer('role_two')->nullable();
      $table->integer('role_three')->nullable();
      $table->integer('role_four')->nullable();
      $table->integer('role_five')->nullable();

      $table->unique(['role_one', 'role_two', 'role_three', 'role_four', 'role_five'], 'compositions_unique');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('compositions');
  }
}
