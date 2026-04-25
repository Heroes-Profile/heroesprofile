<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id('award_table_id');
            $table->integer('award_id');
            $table->string('title')->collation('utf8mb4_0900_ai_ci');
            $table->string('icon')->collation('utf8mb4_0900_ai_ci');
            $table->unique(['award_id', 'title', 'icon'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('awards');
    }
}
