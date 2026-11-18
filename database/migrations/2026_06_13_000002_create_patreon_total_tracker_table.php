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
        Schema::create('patreon_total_tracker', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('patreon_total_tracker_id', true);
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('goal', 10, 2)->nullable()->default(850.00);
            $table->dateTime('date_added')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patreon_total_tracker');
    }
};
