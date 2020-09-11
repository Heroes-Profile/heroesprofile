<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HeroesDataTalents extends Migration
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
    $this->schema->create('heroes_data_talents', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('talent_id')->autoIncrement();
      $table->string('hero_name', 50);
      $table->string('short_name', 50);
      $table->string('attribute_id', 10);
      $table->string('title', 50);
      $table->string('talent_name', 100);
      $table->string('description', 500);
      $table->string('status', 45)->nullable();
      $table->string('hotkey', 100);
      $table->string('cooldown', 10);
      $table->string('mana_cost', 10);
      $table->string('sort', 10);
      $table->integer('level');
      $table->string('icon', 100);
      $table->integer('required_talent_id')->nullable();

      $table->unique(['hero_name', 'title', 'talent_name'], 'heroes_data_talents_Unique_Index');
      $table->index('hero_name');
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
    $this->schema->dropIfExists('heroes_data_talents');
  }
}
