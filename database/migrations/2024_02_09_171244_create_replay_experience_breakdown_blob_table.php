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
            $table->id('replay_experience_breakdown_id');
            $table->unsignedBigInteger('replayID');
            $table->unsignedTinyInteger('team');
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
