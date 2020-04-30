<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplayBansTable extends Migration
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
        $this->schema->create('replay_bans', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('ban_id')->autoIncrement();
          $table->integer('replayID');
          $table->tinyInteger('team');
          $table->integer('hero');
          $table->index(['replayID', 'team', 'hero']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('replay_bans');
    }
}
