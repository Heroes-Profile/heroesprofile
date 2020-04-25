<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterMmrDataTable extends Migration
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
        $this->schema->create('master_mmr_data', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('type_value');
          $table->tinyInteger('game_type');
          $table->integer('blizz_id');
          $table->tinyInteger('region');
          $table->double('conservative_rating');
          $table->double('mean');
          $table->double('standard_deviation');
          $table->integer('win');
          $table->integer('loss');

          $table->primary(['type_value', 'game_type', 'blizz_id', 'region'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('master_mmr_data');
    }
}
