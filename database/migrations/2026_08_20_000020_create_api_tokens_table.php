<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill of Spark's `api_tokens`, which production already has and neither repo
 * had a migration for. Reproduced so a local database built from migrations alone
 * can exercise the activate-live-data path, which expires these rows.
 *
 * Nothing here is written by the new site except `expires_at` at activation. The
 * old site owns the rest.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('api_tokens')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('api_tokens', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('token', 100)->unique();
            $table->text('metadata');
            $table->tinyInteger('transient')->default(0);
            $table->integer('calls')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_tokens');
    }
};
