<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Xal'atath event recap snapshot: one row per page section, data is a JSON array.
 *
 * Filled once after the event by a raw SQL snapshot run by hand.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('xalatath_recap')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('xalatath_recap', function (Blueprint $table) {
            $table->string('section', 32)->primary();
            $table->json('data');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('xalatath_recap');
    }
};
