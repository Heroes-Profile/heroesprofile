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
        Schema::create('patreon_total_tracker', function (Blueprint $table) {
            $table->integer('patreon_total_tracker_id')->autoIncrement();
            $table->decimal('total', 10, 2)->nullable();
            $table->dateTime('date_added')->useCurrent()->nullable();
        });

        DB::statement('ALTER TABLE `patreon_total_tracker` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patreon_total_tracker');
    }
};
