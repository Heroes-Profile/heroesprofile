<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeagueBreakdownsTable extends Migration
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
          $table->integer('type_role_hero');
          $table->tinyInteger('game_type');
          $table->tinyInteger('league_tier');
          $table->double('min_mmr');

          $table->primary(['type_role_hero', 'game_type', 'league_tier'], 'Primary_Index');
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
