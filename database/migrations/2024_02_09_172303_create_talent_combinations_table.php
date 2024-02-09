<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTalentCombinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('talent_combinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('hero')->nullable();
            $table->unsignedInteger('level_one')->nullable();
            $table->unsignedInteger('level_four')->nullable();
            $table->unsignedInteger('level_seven')->nullable();
            $table->unsignedInteger('level_ten')->nullable();
            $table->unsignedInteger('level_thirteen')->nullable();
            $table->unsignedInteger('level_sixteen')->nullable();
            $table->unsignedInteger('level_twenty')->nullable();

            // Indexes
            $table->unique(['hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'], 'Unique');
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
