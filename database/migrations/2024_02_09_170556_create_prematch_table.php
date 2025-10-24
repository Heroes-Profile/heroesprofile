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
            $table->increments('prematch_id');
            $table->unsignedBigInteger('prematch_replayID')->nullable();
            $table->unsignedInteger('game_type')->nullable();
            $table->unsignedInteger('game_map')->nullable();
            $table->unsignedInteger('team')->nullable();
            $table->string('battletag', 45)->nullable();
            $table->unsignedInteger('blizz_id')->nullable();
            $table->unsignedInteger('region')->nullable();
            $table->unsignedInteger('hero')->nullable();
            $table->unsignedInteger('talent_one')->nullable();
            $table->unsignedInteger('talent_four')->nullable();
            $table->unsignedInteger('talent_seven')->nullable();
            $table->unsignedInteger('talent_ten')->nullable();
            $table->unsignedInteger('talent_thirteen')->nullable();
            $table->unsignedInteger('talent_sixteen')->nullable();
            $table->unsignedInteger('talent_twenty')->nullable();
            $table->dateTime('date_added')->default(now());

            $table->index('prematch_replayID', 'prematchID');
            $table->unique(['game_type', 'game_map', 'team', 'battletag', 'blizz_id', 'region'], 'UNIQUE');
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
