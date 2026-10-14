<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where each archived replay sits: one daily bundle object in
 * gs://heroesprofile-replay-archive, read back with a byte-range request.
 *
 * Written by the nightly archiver on the backend VM.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('replay_archive')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('replay_archive', function (Blueprint $table) {
            $table->unsignedInteger('replayID')->primary();
            $table->string('bundle', 64);
            $table->unsignedBigInteger('byte_offset');
            $table->unsignedInteger('byte_length');
            $table->dateTime('archived_at');

            $table->index('bundle', 'bundle');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `replay_archive` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('replay_archive');
    }
};
