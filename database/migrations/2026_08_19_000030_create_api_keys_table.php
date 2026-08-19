<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the old `api_tokens` table, which stored 60-character keys in plaintext.
 * Only a hash is kept here — the key itself is unrecoverable after creation.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('api_keys')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('api_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('api_account_id');
            $table->string('name');
            $table->string('secret_hash', 64)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['api_account_id', 'revoked_at'], 'idx_account_active');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_keys');
    }
};
