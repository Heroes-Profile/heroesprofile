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
            $table->string('key', 750)->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci')->primary();
            $table->mediumText('value')->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci');
            $table->integer('expiration');
            $table->index('expiration');
        });

        // Required by the database cache driver's atomic locks. `Cache::lock()` is
        // what serialises billing changes, so without this a subscribe throws before
        // it reaches Stripe.
        Schema::connection('heroesprofile_cache')->create('cache_locks', function (Blueprint $table) {
            $table->string('key', 255)->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci')->primary();
            $table->string('owner', 255)->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('heroesprofile_cache')->dropIfExists('cache_locks');
        Schema::connection('heroesprofile_cache')->dropIfExists('cache');
    }
}
