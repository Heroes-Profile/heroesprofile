<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTalentCombinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.talent_combinations', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('talent_combination_id')->autoIncrement()->unsigned();
          $table->integer('hero');
          $table->integer('level_one');
          $table->integer('level_four');
          $table->integer('level_seven');
          $table->integer('level_ten');
          $table->integer('level_thirteen');
          $table->integer('level_sixteen');
          $table->integer('level_twenty');

          $table->unique(['hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'], "Unique 1");
          $table->index(['hero', 'level_twenty'], "Index 1");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.talent_combinations');
    }
}
