<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProfilePage extends Migration
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
    $this->schema->create('profile_page', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('profile_page_id')->autoIncrement();
      $table->integer('blizz_id');
      $table->integer('region');
      $table->integer('game_type')->nullable();
      $table->integer('season')->nullable();
      $table->integer('wins');
      $table->integer('losses');
      $table->integer('first_to_ten_wins');
      $table->integer('first_to_ten_losses');
      $table->integer('second_to_ten_wins');
      $table->integer('second_to_ten_losses');
      $table->integer('bruiser_wins');
      $table->integer('bruiser_losses');
      $table->integer('support_wins');
      $table->integer('support_losses');
      $table->integer('ranged_assassin_wins');
      $table->integer('ranged_assassin_losses');
      $table->integer('melee_assassin_wins');
      $table->integer('melee_assassin_losses');
      $table->integer('healer_wins');
      $table->integer('healer_losses');
      $table->integer('tank_wins');
      $table->integer('tank_losses');
      $table->integer('total_time_played');
      $table->integer('account_level');
      $table->integer('kills');
      $table->integer('deaths');
      $table->integer('takedowns');
      $table->longText('hero_data');
      $table->longText('map_data');
      $table->longText('matches');
      $table->integer('latest_replayID');

      $table->primary('profile_page_id');
      $table->unique(['blizz_id', 'region', 'game_type', 'season'], "profile_page_unique");
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('profile_page');
  }
}
