<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MasterMmrDataAr extends Migration
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
    $this->schema->create('master_mmr_data_ar', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('master_mmr_data_ar_id')->autoIncrement();
      $table->integer('type_value');
      $table->tinyInteger('game_type');
      $table->integer('blizz_id');
      $table->tinyInteger('region');
      $table->double('conservative_rating');
      $table->double('mean');
      $table->double('standard_deviation');
      $table->integer('win');
      $table->integer('loss');

      $table->primary('master_mmr_data_ar_id');
      $table->unique(['type_value', 'game_type', 'blizz_id', 'region'], 'master_mmr_data_ar_unique');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('master_mmr_data_ar');
  }
}
