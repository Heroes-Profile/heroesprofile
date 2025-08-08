<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRosterTableCcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ccl.roster', function (Blueprint $table) {
            $table->increments('roster_id');
            $table->integer('season')->default(1);
            $table->integer('team_id')->nullable();
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ccl.roster');
    }
}
