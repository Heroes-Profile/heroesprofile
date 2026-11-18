<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrematchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prematch', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('prematch_id', true);
            $table->integer('prematch_replayID')->nullable();
            $table->integer('game_type')->nullable();
            $table->integer('game_map')->nullable();
            $table->integer('team')->nullable();
            $table->string('battletag', 45)->nullable();
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->integer('hero')->nullable();
            $table->integer('talent_one')->nullable();
            $table->integer('talent_four')->nullable();
            $table->integer('talent_seven')->nullable();
            $table->integer('talent_ten')->nullable();
            $table->integer('talent_thirteen')->nullable();
            $table->integer('talent_sixteen')->nullable();
            $table->integer('talent_twenty')->nullable();
            $table->dateTime('date_added')->nullable()->useCurrent();

            $table->index('prematch_replayID', 'prematchID');
            $table->index(['game_type', 'game_map', 'team', 'battletag', 'blizz_id', 'region'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prematch');
    }
}
