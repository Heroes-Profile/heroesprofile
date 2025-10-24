<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterMmrDataArTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_mmr_data_ar', function (Blueprint $table) {
            $table->increments('master_mmr_data_ar_id');
            $table->unsignedBigInteger('type_value');
            $table->unsignedTinyInteger('game_type');
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->double('conservative_rating');
            $table->double('mean');
            $table->double('standard_deviation');
            $table->unsignedInteger('win');
            $table->unsignedInteger('loss');

            $table->unique(['type_value', 'game_type', 'blizz_id', 'region'], 'UNIQUE');
            $table->index(['win', 'loss', 'conservative_rating'], 'INDEX-FIX');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_mmr_data_ar');
    }
}
