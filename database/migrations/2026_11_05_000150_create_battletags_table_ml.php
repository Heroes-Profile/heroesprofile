<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattletagsTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.battletags', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('player_id', true);
            $table->integer('team_id')->nullable();
            $table->integer('blizz_id');
            $table->string('battletag', 45);
            $table->tinyInteger('region');

            $table->index('battletag', 'battletag');
            $table->index(['battletag', 'region'], 'battletag_region');
            $table->unique(['team_id', 'blizz_id', 'battletag', 'region'], 'BlizzID_Battletag_Region');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.battletags');
    }
}
