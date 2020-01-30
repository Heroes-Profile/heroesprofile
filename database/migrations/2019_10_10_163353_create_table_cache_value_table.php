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
          $table->string('table_to_cache', 45);
          $table->integer('cache_number')->autoIncrement();
          $table->dateTime('date_cached');

          $table->primary(['cache_number', 'table_to_cache'], "Primary_Index");
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
