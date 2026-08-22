<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The four columns the new portal needs on the shared `users` table.
 *
 * They are declared in `create_api_users_table`, but that migration returns early
 * when `users` already exists — which it does everywhere except a fresh local build.
 * So production had no migration for them at all, and they could only have arrived by
 * hand. This closes that: guarded per column, so it is a no-op wherever they are
 * already present and the fix wherever they are not.
 *
 * Dated ahead of `add_admin_to_api_users`, which positions its columns with
 * `after('test_mode')` and therefore cannot run first on a database that has neither.
 *
 * Additive: the old API site reads none of these.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        // The migration gate. `migrated` is one-way and expires the account's old
        // token; `test_mode` is the toggle back to fixtures at any time.
        if (! $schema->hasColumn('users', 'migrated')) {
            $schema->table('users', function (Blueprint $table) {
                $table->boolean('migrated')->default(false);
                $table->boolean('test_mode')->default(false);
            });
        }

        // Cashier's payment method, alongside Spark's `card_brand` / `card_last_four`
        // rather than replacing them — the old site still reads its own pair.
        if (! $schema->hasColumn('users', 'pm_type')) {
            $schema->table('users', function (Blueprint $table) {
                $table->string('pm_type')->nullable();
                $table->string('pm_last_four', 4)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table('users', function (Blueprint $table) {
            $table->dropColumn(['migrated', 'test_mode', 'pm_type', 'pm_last_four']);
        });
    }
};
