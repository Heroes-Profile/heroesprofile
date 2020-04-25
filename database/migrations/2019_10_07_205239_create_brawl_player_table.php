<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrawlPlayerTable extends Migration
{

    /**
     * The database schema.
     *
     * @var DB
     */
    protected $connection;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = DB::connection(config('database.brawl'));
    }


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
        $this->schema = Schema::connection(config('database.brawl'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('player', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->integer('blizz_id');
          $table->string('battletag', 50);
          $table->tinyInteger('hero');
          $table->smallInteger('hero_level');
          $table->smallInteger('mastery_taunt');
          $table->tinyInteger('team');
          $table->tinyInteger('winner');
          $table->string('party', 45);

          $table->primary(['replayID', 'battletag', 'hero']);
          $table->index(['replayID', 'blizz_id', 'hero']);
          $table->index(['blizz_id', 'hero']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('player');
    }
}
