<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Normalized API endpoint registry for the public API: one row per endpoint, one
 * row per (endpoint, plan) quota.
 *
 * Replaces — for the new site only — the hand-maintained `endpoints` wide table
 * and the eight-branch UNION in `endpoint_view`. In the old schema each plan is
 * both a column and a UNION branch, so adding a plan needs a schema change plus a
 * view edit. That is how plans 7 (ccl) and 10 (masters_clash) ended up present in
 * `subscription_plans` but absent from the view, leaving subscribers on those
 * plans unable to call any endpoint. Here, adding a plan is an insert.
 *
 * Deliberately additive. `endpoints` and `endpoint_view` are left untouched, so
 * the old API site keeps enforcing quotas exactly as it does today. That means
 * two registries during the transition — see database/sql/README notes in
 * 2026_08_19_api_endpoint_registry.sql. Pointing `endpoint_view` at these tables
 * is an optional later step (2026_08_19_api_endpoint_view_swap.sql); it is not
 * required for the new site and does change old-site behaviour.
 *
 * Schema only. Data is applied by hand from
 * database/sql/2026_08_19_api_endpoint_registry.sql, which is the record of what
 * was actually run against production.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasTable('api_endpoints')) {
            $schema->create('api_endpoints', function (Blueprint $table) {
                $table->increments('endpoint_id');
                $table->string('endpoint')->unique();
                $table->string('name', 45)->nullable();
                $table->string('group_name', 45)->nullable();
                $table->integer('group_sort')->nullable();
                $table->integer('sort')->nullable();
                $table->timestamps();

                $table->index(['group_sort', 'sort'], 'idx_ordering');
            });
        }

        if (! $schema->hasTable('api_endpoint_quotas')) {
            $schema->create('api_endpoint_quotas', function (Blueprint $table) {
                $table->unsignedInteger('endpoint_id');
                $table->unsignedBigInteger('subscription_plan');
                $table->integer('calls_per_week')->nullable();
                $table->timestamps();

                $table->primary(['endpoint_id', 'subscription_plan']);
                $table->index('subscription_plan', 'idx_subscription_plan');

                $table->foreign('endpoint_id')
                    ->references('endpoint_id')->on('api_endpoints')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_endpoint_quotas');
        Schema::connection(self::CONNECTION)->dropIfExists('api_endpoints');
    }
};
