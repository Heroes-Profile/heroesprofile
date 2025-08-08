<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattletagsTableCcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ccl.battletags', function (Blueprint $table) {
            $table->increments('player_id');
            $table->integer('blizz_id');
            $table->string('battletag', 45);
            $table->tinyInteger('region');
            $table->dateTime('latest_game_played')->default('2014-06-26 13:13:34');

            $table->unique(['blizz_id', 'battletag', 'region'], 'BlizzID_Battletag_Region');
            $table->index('battletag');
            $table->index(['battletag', 'region'], 'battletag_region');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ccl.battletags');
    }
}
