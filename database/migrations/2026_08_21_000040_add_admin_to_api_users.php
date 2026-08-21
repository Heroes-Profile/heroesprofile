<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin accounts for the API portal.
 *
 * Two flags rather than one. `admin` is the grant — set in the database, never
 * self-service, because an account that could grant itself admin is not a
 * permission. `admin_mode` is whether the grant is currently being exercised, and
 * that one the admin toggles for themselves: switching it off makes the portal
 * treat them as an ordinary account so they can see what a customer sees, and the
 * grant is still there to switch it back on.
 *
 * Additive: the old API site reads neither.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasColumn('users', 'admin')) {
            $schema->table('users', function (Blueprint $table) {
                $table->boolean('admin')->default(false)->after('test_mode');
                $table->boolean('admin_mode')->default(true)->after('admin');
            });
        }
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table('users', function (Blueprint $table) {
            $table->dropColumn(['admin', 'admin_mode']);
        });
    }
};
