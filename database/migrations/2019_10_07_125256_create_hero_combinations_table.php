<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroCombinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.hero_combinations', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('hero_comb_id')->autoIncrement();
          $table->tinyInteger('h_1');
          $table->tinyInteger('h_2');
          $table->tinyInteger('h_3');
          $table->tinyInteger('h_4');
          $table->tinyInteger('h_5');
          $table->string('combination', 45);
          $table->unique(['h_1', 'h_2', 'h_3', 'h_4', 'h_5']);
        });
        DB::statement('ALTER TABLE hero_combinations ADD FULLTEXT full(combination)');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hero_combinations');
    }
}
