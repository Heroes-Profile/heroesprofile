<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroMapChangeTableCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.global_hero_map_change', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('global_hero_map_change_id', true);
            $table->string('game_version', 45);
            $table->integer('game_type');
            $table->integer('hero');
            $table->integer('game_map');
            $table->double('win_rate')->nullable();
            $table->double('ban_rate')->nullable();
            $table->integer('games_played')->nullable();
            $table->integer('wins')->nullable();
            $table->integer('losses')->nullable();
            $table->integer('bans')->nullable();

            $table->unique(['game_version', 'game_type', 'hero', 'game_map'], 'unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_cache.global_hero_map_change');
    }
}
