<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayBansTableNgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ngs.replay_bans', function (Blueprint $table) {
            $table->increments('ban_id');
            $table->integer('replayID')->unsigned();
            $table->unsignedTinyInteger('team');
            $table->unsignedInteger('hero');

            $table->index(['replayID', 'team', 'hero'], 'index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ngs.replay_bans');
    }
}
