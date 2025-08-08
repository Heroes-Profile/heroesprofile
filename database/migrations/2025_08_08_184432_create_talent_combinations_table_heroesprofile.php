<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTalentCombinationsTableHeroesprofile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('talent_combinations', function (Blueprint $table) {
            $table->increments('talent_combination_id');
            $table->integer('hero')->nullable();
            $table->integer('level_one')->nullable();
            $table->integer('level_four')->nullable();
            $table->integer('level_seven')->nullable();
            $table->integer('level_ten')->nullable();
            $table->integer('level_thirteen')->nullable();
            $table->integer('level_sixteen')->nullable();
            $table->integer('level_twenty')->nullable();

            $table->unique([
                'hero', 'level_one', 'level_four', 'level_seven', 
                'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'
            ], 'Unique');

            $table->index(['hero', 'level_twenty'], 'index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('talent_combinations');
    }
}
