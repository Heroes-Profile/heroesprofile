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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->string('key', 750);
            $table->mediumText('value');
            $table->integer('expiration');

            $table->primary('key');
            $table->index('expiration', 'expiration');
        });

        // Required by the database cache driver's atomic locks. `Cache::lock()` is
        // what serialises billing changes, so without this a subscribe throws before
        // it reaches Stripe.
        Schema::connection('heroesprofile_cache')->create('cache_locks', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->string('key');
            $table->string('owner');
            $table->integer('expiration');

            $table->primary('key');
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
