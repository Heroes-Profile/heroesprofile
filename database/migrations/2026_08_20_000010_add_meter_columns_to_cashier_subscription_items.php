<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cashier 16 writes these on every subscription item it creates or updates, even
 * for prices with no meter attached — without them, subscribing fails on an
 * unknown column. Cashier ships them as two separate migrations against its own
 * `subscription_items`; ours is one against the renamed table.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('cashier_subscription_items')) {
            return;
        }

        $schema->table('cashier_subscription_items', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('cashier_subscription_items', 'meter_id')) {
                $table->string('meter_id')->nullable()->after('stripe_price');
            }

            if (! $schema->hasColumn('cashier_subscription_items', 'meter_event_name')) {
                $table->string('meter_event_name')->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table('cashier_subscription_items', function (Blueprint $table) {
            $table->dropColumn(['meter_id', 'meter_event_name']);
        });
    }
};
