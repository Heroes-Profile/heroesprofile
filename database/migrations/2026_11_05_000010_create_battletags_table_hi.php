<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattletagsTableHi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_hi.battletags', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('player_id', true);
            $table->integer('blizz_id');
            $table->string('battletag', 45);
            $table->dateTime('latest_game_played')->nullable()->default('2014-06-26 13:13:34');

            $table->index('battletag', 'battletag');
            $table->index('battletag', 'battletag_region');
            $table->unique(['blizz_id', 'battletag'], 'BlizzID_Battletag_Region');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_hi.battletags');
    }
}
