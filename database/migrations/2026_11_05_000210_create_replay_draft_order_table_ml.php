<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayDraftOrderTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.replay_draft_order', function (Blueprint $table) {
            $table->increments('replay_draft_order_id');
            $table->integer('replayID');
            $table->integer('pick_number');
            $table->string('type', 45);
            $table->integer('player_slot')->nullable();
            $table->integer('hero');

            $table->unique(['replayID', 'type', 'pick_number', 'hero'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.replay_draft_order');
    }
}
