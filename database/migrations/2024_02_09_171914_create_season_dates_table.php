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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->increments('id');
            $table->unsignedInteger('year');
            $table->double('season')->unsigned();
            // Entered in Eastern time. start_date/end_date are the UTC values every
            // system compares game_date (UTC) against.
            $table->dateTime('start_date_est');
            $table->dateTime('end_date_est');
            $table->dateTime('start_date')->storedAs('convert_tz(`start_date_est`,_utf8mb4\'America/New_York\',_utf8mb4\'UTC\')');
            $table->dateTime('end_date')->storedAs('convert_tz(`end_date_est`,_utf8mb4\'America/New_York\',_utf8mb4\'UTC\')');
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
