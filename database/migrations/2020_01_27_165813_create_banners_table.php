<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.banners', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('banner_id');
          $table->string('title', 255);
          $table->string('hyperlink_id', 255);
          $table->string('attribute_id', 45);
          $table->string('rarity', 45);
          $table->string('category', 45);
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
        Schema::dropIfExists('heroesprofile.banners');
    }
}
