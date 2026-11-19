<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplayDownloadsNotApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replay_downloads_not_api', function (Blueprint $table) {
            $table->bigIncrements('replay_downloads_not_api_id');
            $table->unsignedInteger('replayID')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('battletag')->nullable();
            $table->dateTime('date_pulled')->nullable();

            $table->index(['battletag', 'date_pulled'], 'replay_downloads_not_api_battletag_date_pulled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replay_downloads_not_api');
    }
}
