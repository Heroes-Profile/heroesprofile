<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBattletagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.battletags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('player_id')->autoIncrement()->unsigned();
            $table->integer('blizz_id');
            $table->string('battletag', 45);
            $table->tinyInteger('region');
            $table->integer('account_level')->nullable();
            $table->tinyInteger('patreon')->nullable();
            $table->tinyInteger('opt_out')->nullable();
            $table->dateTime('latest_game')->default('2014-06-26 13:13:34');
            
            $table->unique(['blizz_id', 'battletag', 'region']);
            $table->index('battletag');
            $table->index('patreon');
            $table->index('opt_out');
            $table->index(['battletag', 'region']);
            $table->index('account_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.battletags');
    }
}
