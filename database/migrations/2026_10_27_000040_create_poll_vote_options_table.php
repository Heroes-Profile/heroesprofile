<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The options picked in each vote.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_polls';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('poll_vote_options')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('poll_vote_options', function (Blueprint $table) {
            $table->unsignedInteger('poll_vote_id');
            $table->unsignedInteger('poll_option_id');

            $table->primary(['poll_vote_id', 'poll_option_id']);
            $table->index('poll_option_id', 'poll_vote_options_option_index');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `poll_vote_options` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('poll_vote_options');
    }
};
