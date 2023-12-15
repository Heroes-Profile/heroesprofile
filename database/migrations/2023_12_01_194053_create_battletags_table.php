<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattletagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battletags', function (Blueprint $table) {
            $table->id('player_id');
            $table->unsignedBigInteger('blizz_id');
            $table->string('battletag');
            $table->tinyInteger('region');
            $table->integer('account_level')->default(0);
            $table->tinyInteger('patreon')->nullable();
            $table->tinyInteger('opt_out')->nullable();
            $table->dateTime('latest_game')->default('2014-06-26 13:13:34');

            // Indexes
            $table->unique(['blizz_id', 'battletag', 'region'], 'BlizzID_Battletag_Region');
            $table->index('battletag');
            $table->index('patreon');
            $table->index('opt_out');
            $table->index(['battletag', 'region'], 'battletag_region');
            $table->index('account_level', null, null, 'INVISIBLE');
            $table->index(['region', 'account_level', 'latest_game'], 'region_accountLevel_latestGame');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('battletags');
    }
}
