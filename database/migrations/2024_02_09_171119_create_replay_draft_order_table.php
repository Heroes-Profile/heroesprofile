<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayDraftOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replay_draft_order', function (Blueprint $table) {
            $table->id('replay_draft_order_id');
            $table->unsignedBigInteger('replayID');
            $table->unsignedInteger('pick_number');
            $table->string('type', 45);
            $table->integer('player_slot')->nullable();
            $table->unsignedBigInteger('hero');
            $table->timestamps(); // Add this line if you want timestamps

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
        Schema::dropIfExists('replay_draft_order');
    }
}
