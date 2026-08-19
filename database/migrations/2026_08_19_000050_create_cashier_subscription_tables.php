<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cashier's tables under non-default names. The old API site keeps using
 * `subscriptions` until the cutover, and the column names differ anyway
 * (`type` vs `name`, `stripe_price` vs `stripe_plan`).
 *
 * `user_id` is unsignedInteger, not the usual foreignId, because `users.id` is
 * int unsigned rather than bigint.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('cashier_subscriptions')) {
            $schema->create('cashier_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('type');
                $table->string('stripe_id')->unique();
                $table->string('stripe_status');
                $table->string('stripe_price')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'stripe_status']);
            });
        }

        if (! $schema->hasTable('cashier_subscription_items')) {
            $schema->create('cashier_subscription_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->string('stripe_id')->unique();
                $table->string('stripe_product');
                $table->string('stripe_price');
                $table->integer('quantity')->nullable();
                $table->timestamps();

                $table->unique(['subscription_id', 'stripe_price']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('cashier_subscription_items');
        Schema::connection(self::CONNECTION)->dropIfExists('cashier_subscriptions');
    }
};
