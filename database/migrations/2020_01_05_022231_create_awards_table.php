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
        Schema::create('heroesprofile.awards', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('award_id');
          $table->string('title', 45);
          $table->string('icon', 45);


          $table->primary(['award_id', 'title', 'icon'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.awards');
    }
}
