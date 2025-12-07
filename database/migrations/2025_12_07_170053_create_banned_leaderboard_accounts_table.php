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
        Schema::create('banned_leaderboard_accounts', function (Blueprint $table) {
            $table->unsignedInteger('banned_leaderboard_accounts')->autoIncrement();
            $table->string('battletag', 45)->nullable();
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->integer('season')->nullable();
            $table->text('info')->nullable();
            
            $table->index(['battletag', 'blizz_id', 'season'], 'index');
            $table->index('season');
            $table->index('region');
        });
        
        // Set charset and collation
        DB::statement('ALTER TABLE `banned_leaderboard_accounts` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
        
        // Make the composite index invisible (MySQL 8.0+)
        DB::statement('ALTER TABLE `banned_leaderboard_accounts` ALTER INDEX `index` INVISIBLE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banned_leaderboard_accounts');
    }
};
