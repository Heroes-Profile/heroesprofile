<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTalentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.talents', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('replayID');
          $table->string('battletag', 50);
          $table->integer('level_one');
          $table->integer('level_four');
          $table->integer('level_seven');
          $table->integer('level_ten');
          $table->integer('level_thirteen');
          $table->integer('level_sixteen');
          $table->integer('level_twenty');
          
          $table->primary(['replayID', 'battletag']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.talents');
    }
}
