<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalPartyChangeTableCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.global_party_change', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('global_party_change_id', true);
            $table->string('game_version', 45);
            $table->integer('game_type');
            $table->integer('hero');
            $table->string('team_ally_stack_value', 5);
            $table->string('team_enemy_stack_value', 5);
            $table->double('win_rate')->nullable();
            $table->integer('games_played')->nullable();
            $table->integer('wins')->nullable();
            $table->integer('losses')->nullable();

            $table->unique(['game_version', 'game_type', 'hero', 'team_ally_stack_value', 'team_enemy_stack_value'], 'unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_cache.global_party_change');
    }
}
