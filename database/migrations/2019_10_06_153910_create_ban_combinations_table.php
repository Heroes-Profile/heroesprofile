<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBanCombinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.ban_combinations', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('id')->autoIncrement()->unsigned();
            $table->tinyInteger('b_1');
            $table->tinyInteger('b_2');
            $table->tinyInteger('b_3');
            $table->unique(['b_1', 'b_2', 'b_3']);
            $table->index(['b_1', 'b_3']);
            $table->index(['b_2', 'b_3']);
            $table->index('b_1');
            $table->index('b_2');
            $table->index('b_3');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ban_combinations');
    }
}
