<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptoutBattletagsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
    public function up()
    {
      Schema::create('heroesprofile_optout.battletags', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('player_id')->unsigned();
          $table->integer('blizz_id');
          $table->string('battletag', 45);
          $table->tinyInteger('region');

          $table->unique(['blizz_id', 'battletag', 'region']);

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('heroesprofile_optout.');

    }
}
