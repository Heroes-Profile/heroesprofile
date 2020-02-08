<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.announcers', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('announcer_id');
          $table->string('title', 255);
          $table->string('hyperlink_id', 255);
          $table->string('attribute_id', 45);
          $table->string('rarity', 45);
          $table->string('category', 45);
          $table->string('gender', 45);
          $table->string('hero', 45);
          $table->dateTime('release_date');
          $table->string('icon', 255);

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
        Schema::dropIfExists('heroesprofile.announcers');
    }
}
