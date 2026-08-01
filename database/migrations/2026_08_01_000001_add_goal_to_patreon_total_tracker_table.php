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
        Schema::table('patreon_total_tracker', function (Blueprint $table) {
            $table->decimal('goal', 10, 2)->nullable()->default(800)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patreon_total_tracker', function (Blueprint $table) {
            $table->dropColumn('goal');
        });
    }
};
