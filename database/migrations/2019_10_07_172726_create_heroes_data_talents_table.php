<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroesDataTalentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.heroes_data_talents', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('talent_id');
          $table->string('hero_name', 50);
          $table->string('short_name', 50);
          $table->string('attribute_id', 10);
          $table->string('title', 50);
          $table->string('talent_name', 100);
          $table->string('description', 500);
          $table->string('status', 45);
          $table->string('hotkey', 100);
          $table->string('cooldown', 10);
          $table->string('mana_cost', 10);
          $table->string('sort', 10);
          $table->string('icon', 100);
          $table->integer('level');

          $table->primary(['talent_id', 'hero_name', 'title', 'talent_name', 'level'], 'Primary_Index');
          $table->unique(['hero_name', 'title', 'talent_name', 'level'], 'Unique_Index');
          $table->index('hero_name');
          $table->index('attribute_id');
          $table->index(['hero_name', 'title', 'talent_name'], 'Index');

        });
        DB::statement("ALTER TABLE heroesprofile.heroes_data_talents CHANGE COLUMN talent_id talent_id INT(11) NOT NULL AUTO_INCREMENT");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.heroes_data_talents');
    }
}
