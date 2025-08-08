<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattletagsTableNgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ngs.battletags', function (Blueprint $table) {
            $table->integer('player_id', true); // auto-increment primary key
            $table->integer('team_id')->nullable();
            $table->integer('blizz_id');
            $table->string('battletag', 45);
            $table->tinyInteger('region');
            $table->unique(['team_id', 'blizz_id', 'battletag', 'region'], 'BlizzID_Battletag_Region');
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
        Schema::dropIfExists('heroesprofile_ngs.battletags');
    }
}
