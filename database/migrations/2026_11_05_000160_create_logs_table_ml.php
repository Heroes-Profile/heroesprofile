<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->integer('replayID')->nullable();
            $table->string('dev_prod', 45)->nullable();
            $table->string('api_key', 250)->nullable();
            $table->string('tournament', 255)->nullable();
            $table->string('season', 45)->nullable();
            $table->string('replay_url', 200)->nullable();
            $table->string('round', 45)->nullable();
            $table->string('game', 45)->nullable();
            $table->string('team_one_name', 200)->nullable();
            $table->string('team_one_player', 45)->nullable();
            $table->string('team_two_name', 200)->nullable();
            $table->string('team_two_player', 45)->nullable();
            $table->string('return_data', 10000)->nullable();
            $table->dateTime('date_sent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.logs');
    }
}
