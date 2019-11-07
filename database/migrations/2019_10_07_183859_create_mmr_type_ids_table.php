<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmrTypeIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.mmr_type_ids', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('mmr_type_id');
          $table->string('name', 45);
          $table->primary('mmr_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.mmr_type_ids');
    }
}
