<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoiceLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.voice_lines', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('voice_lines_id');
          $table->string('title', 255);
          $table->string('hyperlink_id', 255);
          $table->string('attribute_id', 45);
          $table->dateTime('release_date');

          $table->unique(['title']);
          $table->index('attribute_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.voice_lines');
    }
}
