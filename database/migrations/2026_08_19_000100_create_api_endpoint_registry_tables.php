<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Endpoint registry driving per-plan quotas. Replaces the old `endpoints` table,
 * which held one column per plan and needed a schema change to add one.
 *
 * The old `endpoints` and `endpoint_view` are left in place for the old API site.
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
