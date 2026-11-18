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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->bigIncrements('player_table_id');
            $table->unsignedInteger('replayID');
            $table->unsignedInteger('blizz_id');
            $table->integer('battletag');
            $table->unsignedTinyInteger('hero');
            $table->unsignedSmallInteger('hero_level');
            $table->unsignedSmallInteger('mastery_taunt')->nullable();
            $table->unsignedTinyInteger('team');
            $table->unsignedTinyInteger('winner');
            $table->integer('party')->nullable();
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
            $table->double('map_conservative_rating')->nullable();
            $table->double('map_mean')->nullable();
            $table->double('map_standard_deviation')->nullable();
            $table->double('map_change')->nullable();
            $table->dateTime('mmr_date_parsed')->nullable();

            $table->index('battletag', 'battletag');
            $table->index(['blizz_id', 'hero'], 'blizzid_hero');
            $table->index(['blizz_id', 'replayID'], 'blizzID_replayID');
            $table->index(['replayID', 'blizz_id', 'hero'], 'replayID_blizzID_Hero');
            $table->unique(['replayID', 'battletag', 'hero'], 'UNIQUE');
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
