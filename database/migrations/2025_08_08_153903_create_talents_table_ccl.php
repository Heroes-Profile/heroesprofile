<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTalentsTableCcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ccl.talents', function (Blueprint $table) {
            $table->unsignedInteger('replayID');
            $table->integer('battletag');
            $table->integer('level_one')->nullable();
            $table->integer('level_four')->nullable();
            $table->integer('level_seven')->nullable();
            $table->integer('level_ten')->nullable();
            $table->integer('level_thirteen')->nullable();
            $table->integer('level_sixteen')->nullable();
            $table->integer('level_twenty')->nullable();

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
        Schema::dropIfExists('heroesprofile_ccl.talents');
    }
}
