<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.maps_translations', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('name', 45);
          $table->string('short_name', 45);
          $table->string('translation', 45);

          $table->primary(['name', 'short_name', 'translation'], 'Primary_Index');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile.maps_translations');
    }
}
