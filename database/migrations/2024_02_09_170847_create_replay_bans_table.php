<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replay_bans', function (Blueprint $table) {
            $table->id('ban_id');
            $table->unsignedBigInteger('replayID');
            $table->unsignedTinyInteger('team');
            $table->unsignedBigInteger('hero');

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
        Schema::dropIfExists('replay_bans');
    }
}
