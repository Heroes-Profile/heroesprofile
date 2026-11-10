<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayBansTableNutcup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_nutcup.replay_bans', function (Blueprint $table) {
            $table->increments('ban_id');
            $table->integer('replayID');
            $table->unsignedInteger('hero');

            $table->index(['replayID', 'hero'], 'index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_nutcup.replay_bans');
    }
}
