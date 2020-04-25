<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaderboardTable extends Migration
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
        $this->schema->create('leaderboard', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('leaderboard_id')->autoIncrement();
          $table->integer('game_type');
          $table->integer('season');
          $table->integer('type');
          $table->integer('rank');
          $table->string('split_battletag', 255);
          $table->string('battletag', 255);
          $table->integer('blizz_id');
          $table->tinyInteger('region');
          $table->double('win_rate');
          $table->integer('win');
          $table->integer('loss');
          $table->integer('games_played');
          $table->double('conservative_rating');
          $table->double('rating');
          $table->integer('cache_number');

          $table->unique(['game_type', 'season', 'type', 'rank', 'cache_number'], "Base_Unique");
          $table->index(['game_type', 'season', 'type', 'cache_number'], "Index 1");
          $table->index(['game_type', 'season', 'type', 'cache_number', 'region'], "Index 2");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('leaderboard');
    }
}
