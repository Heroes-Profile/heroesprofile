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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('player_camera_distance_id', true);
            $table->integer('replayID')->nullable();
            $table->integer('blizz_id')->nullable();
            $table->integer('battletag')->nullable();
            $table->integer('camera_distance')->nullable();

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
