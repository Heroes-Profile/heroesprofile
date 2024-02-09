<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattletagsTableCCL extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('heroesprofile_ccl')->create('battletags', function (Blueprint $table) {
            $table->id('player_id');
            $table->integer('blizz_id')->unsigned();
            $table->string('battletag', 45);
            $table->tinyInteger('region');
            $table->dateTime('latest_game_played')->default('2014-06-26 13:13:34');
            
            // Unique constraints
            $table->unique(['blizz_id', 'battletag', 'region'], 'BlizzID_Battletag_Region');
            
            // Indexes
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
        Schema::connection('heroesprofile_ccl')->dropIfExists('battletags');
    }
}
