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
        Schema::create('match_prediction_player_stats', function (Blueprint $table) {
            $table->integer('match_prediction_player_stats_id')->autoIncrement();
            $table->integer('battlenet_accounts_id')->nullable();
            $table->integer('season')->nullable();
            $table->integer('game_type')->nullable();
            $table->integer('win')->default(0);
            $table->integer('loss')->default(0);

            $table->unique(['battlenet_accounts_id', 'season', 'game_type'], 'UNIQUE');
        });

        DB::statement('ALTER TABLE `match_prediction_player_stats` ADD COLUMN `games_played` int GENERATED ALWAYS AS ((`win` + `loss`)) VIRTUAL');
        DB::statement('ALTER TABLE `match_prediction_player_stats` ADD COLUMN `win_rate` double GENERATED ALWAYS AS (((`win` / nullif((`win` + `loss`),0)) * 100)) VIRTUAL');
        DB::statement('ALTER TABLE `match_prediction_player_stats` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_prediction_player_stats');
    }
};
