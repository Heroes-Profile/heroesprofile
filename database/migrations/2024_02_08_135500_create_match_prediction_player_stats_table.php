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
        Schema::create('match_prediction_player_stats', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('match_prediction_player_stats_id', true);
            $table->integer('battlenet_accounts_id')->nullable();
            $table->integer('season')->nullable();
            $table->integer('game_type')->nullable();
            $table->integer('win')->nullable()->default(0);
            $table->integer('loss')->nullable()->default(0);
            $table->integer('games_played')->virtualAs('(`win` + `loss`)')->nullable();
            $table->double('win_rate')->virtualAs('((`win` / nullif((`win` + `loss`),0)) * 100)')->nullable();

            $table->unique(['battlenet_accounts_id', 'season', 'game_type'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_prediction_player_stats');
    }
};
