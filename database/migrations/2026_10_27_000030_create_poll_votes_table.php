<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One vote per Battle.net account per poll; re-voting replaces it.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_polls';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('poll_votes')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('poll_votes', function (Blueprint $table) {
            $table->increments('poll_vote_id');
            $table->unsignedInteger('poll_id');
            $table->unsignedInteger('battlenet_accounts_id');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['poll_id', 'battlenet_accounts_id'], 'poll_votes_poll_account_unique');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `poll_votes` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('poll_votes');
    }
};
