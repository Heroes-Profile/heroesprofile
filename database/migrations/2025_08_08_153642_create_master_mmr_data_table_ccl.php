<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterMmrDataTableCcl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ccl.master_mmr_data', function (Blueprint $table) {
            $table->increments('master_mmr_data_id');
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->double('conservative_rating');
            $table->double('mean');
            $table->double('standard_deviation');
            $table->unsignedInteger('win');
            $table->unsignedInteger('loss');

            $table->unique(['blizz_id', 'region'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ccl.master_mmr_data');
    }
}
