<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplayExperienceBreakdownTable extends Migration
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
        $this->schema->create('replay_experience_breakdown', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->tinyInteger('team');
          $table->integer('team_level');
          $table->string('timestamp', 500);
          $table->double('structureXP');
          $table->double('creepXP');
          $table->double('heroXP');
          $table->double('minionXP');
          $table->double('trickXP');
          $table->double('totalXP');

          $table->primary(['replayID', 'team', 'team_level', 'timestamp'], 'replay_experience_breakdown_Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('replay_experience_breakdown');
    }
}
