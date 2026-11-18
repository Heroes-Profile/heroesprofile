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
        Schema::create('banned_leaderboard_accounts', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->increments('banned_leaderboard_accounts');
            $table->string('battletag', 45)->nullable();
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->integer('season')->nullable();
            $table->text('info')->nullable();

            $table->index(['battletag', 'blizz_id', 'season'], 'index');
            $table->index('region', 'region');
            $table->index('season', 'season');
        });

        // Set charset and collation

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banned_leaderboard_accounts');
    }
};
