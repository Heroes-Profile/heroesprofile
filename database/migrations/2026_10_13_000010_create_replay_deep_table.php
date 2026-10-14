<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replays whose deep analytics doc has been loaded into BigQuery, and at which
 * extract version. Written by the deep analytics loader on the backend VM; the site
 * reads it to skip replays already processed and to show request progress.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('replay_deep')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('replay_deep', function (Blueprint $table) {
            $table->unsignedInteger('replayID')->primary();
            $table->unsignedSmallInteger('extract_v');
            $table->dateTime('loaded_at');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `replay_deep` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('replay_deep');
    }
};
