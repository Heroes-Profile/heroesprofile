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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('player_id', true);
            $table->integer('blizz_id');
            $table->string('battletag', 45);
            $table->tinyInteger('region');
            $table->integer('account_level')->nullable()->default(0);
            $table->tinyInteger('patreon')->nullable();
            $table->tinyInteger('opt_out')->nullable();
            $table->dateTime('latest_game')->nullable()->default('2014-06-26 13:13:34');

            $table->index('account_level', 'account_level');
            $table->index('battletag', 'battletag');
            $table->index(['battletag', 'region'], 'battletag_region');
            // Indexes
            $table->unique(['blizz_id', 'battletag', 'region'], 'BlizzID_Battletag_Region');
            $table->index('opt_out', 'opt_out');
            $table->index('patreon', 'patreon');
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
