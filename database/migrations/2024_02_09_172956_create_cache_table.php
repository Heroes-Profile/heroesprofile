<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('heroesprofile_cache')->create('cache', function (Blueprint $table) {
            $table->string('key', 750)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->primary();
            $table->mediumText('value')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
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
        Schema::connection('heroesprofile_cache')->dropIfExists('cache');
    }
}
