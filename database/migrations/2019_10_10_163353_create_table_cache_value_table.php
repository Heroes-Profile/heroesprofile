<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCacheValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.table_cache_value', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('table_cache_value_id')->autoIncrement();
          $table->string('table_to_cache', 45);
          $table->integer('season');
          $table->integer('cache_number');
          $table->dateTime('date_cached');

          $table->unique(['table_to_cache', 'season', 'cache_number', ], "Base_Unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_cache.table_cache_value');
    }
}
