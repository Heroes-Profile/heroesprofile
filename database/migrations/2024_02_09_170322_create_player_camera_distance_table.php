<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerCameraDistanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_camera_distance', function (Blueprint $table) {
            $table->id('player_camera_distance_id');
            $table->unsignedBigInteger('replayID')->nullable();
            $table->unsignedBigInteger('blizz_id')->nullable();
            $table->unsignedBigInteger('battletag')->nullable();
            $table->unsignedInteger('camera_distance')->nullable();

            $table->index('camera_distance', 'INDEX1');
            $table->index('blizz_id', 'INDEX2');
            $table->index('replayID', 'INDEX3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_camera_distance');
    }
}
