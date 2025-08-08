<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroChangeTableCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.global_hero_change', function (Blueprint $table) {
            $table->increments('global_hero_change_id');
            $table->string('game_version', 45);
            $table->integer('game_type');
            $table->integer('hero');
            $table->double('win_rate')->nullable();
            $table->double('popularity')->nullable();
            $table->double('ban_rate')->nullable();
            $table->integer('games_played')->nullable();
            $table->integer('wins')->nullable();
            $table->integer('losses')->nullable();
            $table->integer('bans')->nullable();

            $table->unique(['game_version', 'game_type', 'hero'], 'unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cache.global_hero_change');
    }
}
