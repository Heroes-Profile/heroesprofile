<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GlobalHeroChange extends Migration
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
    $this->schema->create('global_hero_change', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('global_hero_change_id')->autoIncrement();
      $table->string('game_version', 45);
      $table->tinyInteger('game_type');
      $table->tinyInteger('hero');
      $table->double('win_rate');
      $table->double('popularity');
      $table->double('ban_rate');
      $table->integer('games_played');
      $table->integer('wins');
      $table->integer('losses');
      $table->integer('bans');


      $table->primary('global_hero_change_id');
      $table->unique(['game_version', 'game_type', 'hero'], 'global_hero_change_Base_Unique');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('global_hero_change');
  }
}
