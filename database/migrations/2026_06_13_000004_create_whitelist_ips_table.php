<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('heroesprofile_logs.whitelist_ips', function (Blueprint $table) {
            $table->integer('whitelist_ips_id')->autoIncrement();
            $table->string('ip', 45)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->string('reason', 45)->nullable()->collation('utf8mb4_0900_ai_ci');
        });

        DB::statement('ALTER TABLE `heroesprofile_logs`.`whitelist_ips` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_logs.whitelist_ips');
    }
};
