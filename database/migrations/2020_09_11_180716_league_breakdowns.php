<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeagueBreakdowns extends Migration
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
    $this->schema->create('league_breakdowns', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('league_breakdowns_id')->autoIncrement();
      $table->integer('type_role_hero');
      $table->tinyInteger('game_type');
      $table->tinyInteger('league_tier');
      $table->double('min_mmr');

      $table->primary('league_breakdowns_id');
      $table->unique(['type_role_hero', 'game_type', 'league_tier'], 'league_breakdowns_Primary_Index');
      $table->index('min_mmr');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('league_breakdowns');
  }
}
