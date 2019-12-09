<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroesTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.heroes_translations', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('name', 45);
          $table->string('short_name', 45);
          $table->string('translation', 45);
          $table->string('attribute_id', 45);

          $table->primary(['name', 'short_name', 'translation', 'attribute_id'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.heroes_translations');
    }
}
