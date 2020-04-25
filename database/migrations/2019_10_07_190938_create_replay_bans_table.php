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
          $table->integer('ban_id');
          $table->integer('replayID');
          $table->tinyInteger('team');
          $table->integer('hero');

          $table->primary('ban_id');
          $table->index(['replayID', 'team', 'hero']);

        });
        DB::statement("ALTER TABLE replay_bans CHANGE COLUMN ban_id ban_id INT(11) NOT NULL AUTO_INCREMENT");

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
