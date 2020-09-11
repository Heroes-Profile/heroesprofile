<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Battletags extends Migration
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
    $this->schema->create('battletags', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('player_id')->autoIncrement();
      $table->integer('blizz_id');
      $table->string('battletag', 45);
      $table->tinyInteger('region');
      $table->integer('account_level')->default(0);
      $table->tinyInteger('patreon')->nullable();
      $table->tinyInteger('opt_out')->nullable();
      $table->dateTime('latest_game')->default('2014-06-26 13:13:34');

      $table->primary('player_id');
      $table->unique(['blizz_id', 'battletag', 'region'], 'battletags_unique');
      $table->index('patreon', 'patreon_index');
      $table->index('opt_out', 'optout_index');
      $table->index(['battletag', 'region'], 'battletag_region_index');
      $table->index('account_level', 'account_level_index');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('battletags');
  }
}
