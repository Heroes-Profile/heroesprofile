<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMmrTypeIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mmr_type_ids', function (Blueprint $table) {
            $table->id('mmr_type_table_id');
            $table->unsignedInteger('mmr_type_id');
            $table->string('name', 45)->collation('utf8mb4_0900_ai_ci');

            $table->unique('mmr_type_id', 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mmr_type_ids');
    }
}
