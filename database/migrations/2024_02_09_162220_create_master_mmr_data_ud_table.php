<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterMmrDataUdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_mmr_data_ud', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('master_mmr_data_ud_id', true);
            $table->integer('type_value');
            $table->unsignedTinyInteger('game_type');
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->double('conservative_rating');
            $table->double('mean');
            $table->double('standard_deviation');
            $table->unsignedInteger('win');
            $table->unsignedInteger('loss');

            $table->index(['type_value', 'game_type', 'conservative_rating', 'win', 'loss'], 'INDEX');
            $table->index(['win', 'loss', 'conservative_rating'], 'INDEX-FIX');
            $table->unique(['type_value', 'game_type', 'blizz_id', 'region'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_mmr_data_ud');
    }
}
