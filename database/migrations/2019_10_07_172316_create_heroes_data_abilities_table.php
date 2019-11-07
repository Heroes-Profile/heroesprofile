<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroesDataAbilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.heroes_data_abilities', function (Blueprint $table) {
          $table->engine = 'InnoDB';

          $table->string('hero_name', 100);
          $table->string('short_name', 100);
          $table->string('attribute_id', 100);
          $table->string('title', 100);
          $table->string('description', 500);
          $table->string('mana_cost', 10);
          $table->string('cooldown', 10);
          $table->string('trait', 10);
          $table->string('name', 10);
          $table->string('hotkey', 10);
          $table->string('icon', 500);
          $table->unique(['hero_name', 'title']);
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
        Schema::dropIfExists('heroes_data_abilities');
    }
}
