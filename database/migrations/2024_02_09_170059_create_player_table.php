<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player', function (Blueprint $table) {
            $table->increments('player_table_id');
            $table->unsignedInteger('replayID');
            $table->unsignedBigInteger('blizz_id');
            $table->string('battletag', 50)->collation('utf8mb4_0900_ai_ci');
            $table->tinyInteger('hero');
            $table->unsignedSmallInteger('hero_level');
            $table->unsignedSmallInteger('mastery_taunt')->nullable();
            $table->unsignedTinyInteger('team');
            $table->unsignedTinyInteger('winner');
            $table->string('party', 45)->nullable();
            $table->integer('stack_size')->nullable();
            $table->double('player_conservative_rating')->nullable();
            $table->double('player_mean')->nullable();
            $table->double('player_standard_deviation')->nullable();
            $table->double('player_change')->nullable();
            $table->double('hero_conservative_rating')->nullable();
            $table->double('hero_mean')->nullable();
            $table->double('hero_standard_deviation')->nullable();
            $table->double('hero_change')->nullable();
            $table->double('role_conservative_rating')->nullable();
            $table->double('role_mean')->nullable();
            $table->double('role_standard_deviation')->nullable();
            $table->double('role_change')->nullable();
            $table->dateTime('mmr_date_parsed')->nullable();

            $table->unique(['replayID', 'battletag', 'hero'], 'UNIQUE');
            $table->index(['replayID', 'blizz_id', 'hero'], 'replayID_blizzID_Hero');
            $table->index(['blizz_id', 'hero'], 'blizzID_hero');
            $table->index(['replayID', 'hero', 'player_conservative_rating'], 'replayID_hero_ConservativeRating');
            $table->index(['hero', 'player_conservative_rating'], 'hero_conservativeratinbg');
            $table->index(['replayID', 'blizz_id', 'mmr_date_parsed'], 'replayID_blizzID_mmrDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player');
    }
}
