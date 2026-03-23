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
            $table->id('replay_downloads_not_api_id');
            $table->unsignedInteger('replayID')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('battletag', 255)->nullable();
            $table->dateTime('date_pulled')->nullable();
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
