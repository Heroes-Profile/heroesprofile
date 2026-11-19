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
        Schema::create('heroesprofile_logs.whitelist_ips', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('whitelist_ips_id', true);
            $table->string('ip', 45)->nullable();
            $table->string('reason', 45)->nullable();

            $table->index('ip', 'whitelist_ips_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_logs.whitelist_ips');
    }
};
