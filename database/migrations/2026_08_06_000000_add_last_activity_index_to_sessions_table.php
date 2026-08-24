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
        Schema::table('heroesprofile_cache.sessions', function (Blueprint $table) {
            $table->index('last_activity', 'idx_last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('heroesprofile_cache.sessions', function (Blueprint $table) {
            $table->dropIndex('idx_last_activity');
        });
    }
};
