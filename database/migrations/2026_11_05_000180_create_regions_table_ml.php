<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionsTableMl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_ml.regions', function (Blueprint $table) {
            $table->integer('region_id')->primary();
            $table->string('short_name', 45)->nullable();
            $table->string('name', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_ml.regions');
    }
}
