<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maps', function (Blueprint $table) {
            $table->id('map_id');
            $table->string('name', 255)->unique();
            $table->string('short_name', 255)->nullable();
            $table->string('type', 45)->nullable();
            $table->tinyInteger('ranked_rotation')->nullable();
            $table->tinyInteger('playable')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maps');
    }
}
