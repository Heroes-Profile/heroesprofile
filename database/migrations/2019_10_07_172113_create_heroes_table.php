<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.heroes', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('id')->autoIncrement()->unsigned();
          $table->string('name', 255);
          $table->string('short_name', 32);
          $table->string('alt_name', 45);
          $table->string('role', 32);
          $table->string('new_role', 45);
          $table->string('type', 32);
          $table->dateTime('release_date');
          $table->dateTime('rework_date');
          $table->char('attribute_id');

          $table->unique('name');
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
        Schema::dropIfExists('heroesprofile.heroes');
    }
}
