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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('replay_draft_order_id', true);
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
        Schema::dropIfExists('replay_draft_order');
    }
}
