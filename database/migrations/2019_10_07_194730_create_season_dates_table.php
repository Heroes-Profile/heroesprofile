<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeasonDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.season_dates', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('id')->unsigned();
          $table->integer('year');
          $table->double('season');
          $table->dateTime('start_date');
          $table->dateTime('end_date');
          $table->primary('id');

        });
        DB::statement("ALTER TABLE heroesprofile.season_dates CHANGE COLUMN id id INT(11) NOT NULL AUTO_INCREMENT");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.season_dates');
    }
}
