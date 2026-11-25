<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replays that are a second copy of a game already stored. replayID is the copy
 * that's hidden, duplicate_of the one that's kept.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('replay_duplicates')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('replay_duplicates', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->unsignedInteger('replayID');
            $table->unsignedInteger('duplicate_of');
            $table->dateTime('date_added');

            $table->primary('replayID');
            $table->index('duplicate_of', 'duplicate_of');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `replay_duplicates` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('replay_duplicates');
    }
};
