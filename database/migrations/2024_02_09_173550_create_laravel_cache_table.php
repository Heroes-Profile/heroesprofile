<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravelCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('heroesprofile_cache')->create('laravel_cache', function (Blueprint $table) {
            $table->string('key', 750)->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci')->primary();
            $table->mediumText('value')->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci');
            $table->integer('expiration');
            $table->index('expiration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('heroesprofile_cache')->dropIfExists('laravel_cache');
    }
}
