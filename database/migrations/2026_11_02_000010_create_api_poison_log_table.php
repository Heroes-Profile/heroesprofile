<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every marked value sent to a poisoned account, next to what the site had at the
 * time, so a republished number can be checked after the live one has moved on.
 *
 * One row per distinct value rather than per call: a repeat bumps `times_sent` and
 * `last_sent_at`, and a new row appears only when the real value changes. Pruned
 * after 90 days on `last_sent_at` by PruneDatabaseTables.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('api_poison_log')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('api_poison_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('api_account_id');

            // The endpoint path and the filters it was called with, minus the key.
            $table->string('endpoint', 100);
            $table->text('params');
            $table->char('params_hash', 64);

            // What the row describes: `hero:12`, `talent:2423`, and a readable name.
            $table->string('identity', 100);
            $table->string('label', 150);

            $table->decimal('real_value', 8, 4);
            $table->decimal('sent_value', 8, 4);

            $table->unsignedInteger('times_sent')->default(1);
            $table->timestamp('first_sent_at');
            $table->timestamp('last_sent_at')->index();

            $table->unique(['api_account_id', 'params_hash', 'identity', 'real_value'], 'uniq_poison_value');
            $table->index(['api_account_id', 'last_sent_at'], 'idx_account_sent');
            $table->index(['label', 'sent_value'], 'idx_label_value');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_poison_log');
    }
};
