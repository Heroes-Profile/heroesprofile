<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTalentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('talents', function (Blueprint $table) {
            $table->unsignedInteger('replayID');
            $table->unsignedInteger('battletag');
            $table->unsignedInteger('level_one')->nullable();
            $table->unsignedInteger('level_four')->nullable();
            $table->unsignedInteger('level_seven')->nullable();
            $table->unsignedInteger('level_ten')->nullable();
            $table->unsignedInteger('level_thirteen')->nullable();
            $table->unsignedInteger('level_sixteen')->nullable();
            $table->unsignedInteger('level_twenty')->nullable();
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
        Schema::dropIfExists('talents');
    }
}
