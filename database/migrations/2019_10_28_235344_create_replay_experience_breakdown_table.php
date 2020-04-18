<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplayExperienceBreakdownTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.replay_experience_breakdown', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->tinyInteger('team');
          $table->integer('team_level');
          $table->string('timestamp', 500);
          $table->double('strutureXP');
          $table->double('creepXP');
          $table->double('heroXP');
          $table->double('minionXP');
          $table->double('trickleXP');
          $table->double('totalXP');

          $table->primary(['replayID', 'team', 'team_level', 'timestamp'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.replay_experience_breakdown');
    }
}
