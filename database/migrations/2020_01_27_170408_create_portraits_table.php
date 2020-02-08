<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortraitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.portraits', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('portrait_id');
          $table->string('title', 255);
          $table->string('hyperlink_id', 255);

          $table->unique(['title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.portraits');
    }
}
