<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayExperienceBreakdownBlobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replay_experience_breakdown_blob', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('replay_experience_breakdown_id', true);
            $table->integer('replayID');
            $table->tinyInteger('team');
            $table->longText('data')->nullable();

            $table->unique(['replayID', 'team'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replay_experience_breakdown_blob');
    }
}
