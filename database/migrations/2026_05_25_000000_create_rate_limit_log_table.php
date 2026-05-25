<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('heroesprofile_logs.rate_limit_log', function (Blueprint $table) {
            $table->id('rate_limit_log_id');
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->string('path', 500)->nullable();
            $table->text('query_string')->nullable();
            $table->string('limiter', 50)->nullable();
            $table->unsignedBigInteger('replay_id')->nullable();
            $table->boolean('is_old_replay')->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->unsignedSmallInteger('retry_after')->nullable();
            $table->timestamp('date_time')->useCurrent()->nullable();
            $table->index('ip', 'idx_ip');
            $table->index('date_time', 'idx_date_time');
            $table->index('limiter', 'idx_limiter');
            $table->index(['ip', 'date_time'], 'idx_ip_date');
            $table->index('replay_id', 'idx_replay_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_logs.rate_limit_log');
    }
};
