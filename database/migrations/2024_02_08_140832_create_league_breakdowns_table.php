<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeagueBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('league_breakdowns', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('league_breakdowns_id', true);
            $table->integer('type_role_hero');
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->double('min_mmr')->nullable();

            $table->index('min_mmr', 'min_mmr');
            $table->unique(['type_role_hero', 'game_type', 'league_tier'], 'UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('league_breakdowns');
    }
}
