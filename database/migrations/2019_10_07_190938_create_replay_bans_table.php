<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplayBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.replay_bans', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('ban_id');
          $table->integer('replayID');
          $table->tinyInteger('team');
          $table->integer('hero');
          $table->primary('ban_id');
          $table->index(['replayID', 'team', 'hero']);

        });
        DB::statement("ALTER TABLE heroesprofile.replay_bans CHANGE COLUMN ban_id ban_id INT(11) NOT NULL AUTO_INCREMENT");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.replay_bans');
    }
}
