<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TalentCombinations extends Migration
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
    $this->schema->create('talent_combinations', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('talent_combination_id')->autoIncrement()->unsigned();
      $table->integer('hero');
      $table->integer('level_one');
      $table->integer('level_four');
      $table->integer('level_seven');
      $table->integer('level_ten');
      $table->integer('level_thirteen');
      $table->integer('level_sixteen');
      $table->integer('level_twenty');

      $table->unique(['hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'], "talent_combinations_Unique 1");
      $table->index(['hero', 'level_twenty'], "talent_combinations_Index 1");
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('talent_combinations');
  }
}
