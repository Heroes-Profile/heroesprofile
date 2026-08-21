<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill of the upload tracking tables, which production already has and
 * neither repo had a migration for. Both live in the API schema because the old
 * API site owns them.
 *
 * `created_at` is filled by the database rather than Eloquent — there is no
 * `updated_at`, so the models keep timestamps off.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('upload_attempts')) {
            $schema->create('upload_attempts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('ip', 45);
                $table->char('file_hash', 64);
                $table->string('file_name', 255);
                $table->unsignedInteger('file_size');
                $table->string('status', 20)->default('Pending');
                $table->timestamp('created_at')->nullable()->useCurrent();

                $table->index(['ip', 'file_hash'], 'idx_ip_file_hash');
            });
        }

        if (! $schema->hasTable('upload_attempt_logs')) {
            $schema->create('upload_attempt_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('ip', 45);
                $table->char('file_hash', 64);
                $table->string('file_name', 255);
                $table->unsignedInteger('file_size');
                $table->string('source', 50)->default('unknown');
                $table->string('status', 50);
                $table->string('message', 255)->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();

                $table->index(['ip', 'created_at'], 'idx_ip_created_at');
                $table->index(['status', 'created_at'], 'idx_status_created_at');
                $table->index('file_hash', 'idx_file_hash');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        $schema->dropIfExists('upload_attempt_logs');
        $schema->dropIfExists('upload_attempts');
    }
};
