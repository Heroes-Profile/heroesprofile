<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-account, per-endpoint usage for the rolling weekly quota.
 *
 * Separate from the old `api_token_calls`, which keys on the token string and so
 * gives a customer a fresh allowance for every key they create. Quota is priced per
 * account, so it is counted per account. The old table is left alone for the old
 * site.
 *
 * The egress columns are generated, mirroring `api_token_calls` — the middleware
 * only ever writes `egress_bytes`.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    private const GIB = '((1024 * 1024) * 1024)';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('api_usage')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('api_usage', function (Blueprint $table) {
            $table->unsignedInteger('api_account_id');
            $table->string('endpoint');
            $table->integer('calls')->default(0);
            $table->bigInteger('egress_bytes')->default(0);
            $table->timestamp('window_started_at')->nullable();

            $table->decimal('egress_mb', 12, 4)
                ->storedAs('((`egress_bytes` / 1024) / 1024)');

            // Tiered GCP egress pricing: $0.085/GiB to 10 TiB, $0.065 to 150 TiB,
            // $0.045 beyond.
            $table->decimal('egress_cost_usd', 14, 6)->storedAs(
                '(case'
                .' when (`egress_bytes` <= 10995116277760)'
                .' then ((`egress_bytes` / '.self::GIB.') * 0.085)'
                .' when (`egress_bytes` <= 164926744166400)'
                .' then ((10240 * 0.085) + (((`egress_bytes` / '.self::GIB.') - 10240) * 0.065))'
                .' else (((10240 * 0.085) + ((153600 - 10240) * 0.065))'
                .' + (((`egress_bytes` / '.self::GIB.') - 153600) * 0.045))'
                .' end)'
            );

            $table->primary(['api_account_id', 'endpoint']);
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_usage');
    }
};
