<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('season_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('year');
            $table->double('season', 10, 2)->unsigned();
            // Entered in Eastern time. start_date/end_date are the UTC values every
            // system compares game_date (UTC) against.
            $table->dateTime('start_date_est');
            $table->dateTime('end_date_est');
            $table->dateTime('start_date')->storedAs("CONVERT_TZ(start_date_est, 'America/New_York', 'UTC')");
            $table->dateTime('end_date')->storedAs("CONVERT_TZ(end_date_est, 'America/New_York', 'UTC')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_dates');
    }
}
