<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMatchPredictionPlayerStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_prediction_player_stats', function (Blueprint $table) {
            $table->increments('match_prediction_player_stats_id');
            $table->integer('battlenet_accounts_id')->nullable();
            $table->integer('season')->nullable();
            $table->integer('game_type')->nullable();
            $table->integer('win')->default(0);
            $table->integer('loss')->default(0);

            $table->unique(['battlenet_accounts_id', 'season', 'game_type'], 'UNIQUE');
        });

        // Add computed columns using raw SQL since Laravel doesn't have direct support
        DB::statement('ALTER TABLE match_prediction_player_stats ADD COLUMN games_played INT GENERATED ALWAYS AS (win + loss) VIRTUAL');
        DB::statement('ALTER TABLE match_prediction_player_stats ADD COLUMN win_rate DOUBLE GENERATED ALWAYS AS ((win / games_played) * 100) VIRTUAL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('match_prediction_player_stats');
    }
}

