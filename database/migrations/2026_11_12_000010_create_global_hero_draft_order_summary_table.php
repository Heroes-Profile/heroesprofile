<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * global_hero_draft_order summed over hero_league_tier, role_league_tier, hero_level and win_loss, so the Drafter's
 * basic filters (timeframe, region, map, player rank) read far fewer rows. Each player adds 0.2 of a pick, as in the full table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('heroesprofile_globals')->create('global_hero_draft_order_summary', function (Blueprint $table) {
            $table->bigInteger('global_hero_draft_order_summary_id');
            $table->integer('game_version');
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('game_map');
            $table->integer('region');
            $table->tinyInteger('hero');
            $table->integer('pick_number')->nullable();
            $table->double('count')->default(0);

            $table->primary(['global_hero_draft_order_summary_id', 'game_version']);
            $table->unique(['game_version', 'game_type', 'league_tier', 'game_map', 'region', 'hero', 'pick_number'], 'unique');
            $table->index(['game_version', 'game_type', 'league_tier', 'game_map', 'region', 'hero', 'pick_number', 'count'], 'Index_Count');
        });

        // Partitioned by game_version like the other global tables; BackendServerApplication's AddNewPartition
        // splits a new p_<id> off p_future for each new patch
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_draft_order_summary` MODIFY `global_hero_draft_order_summary_id` BIGINT NOT NULL AUTO_INCREMENT');
        DB::connection('heroesprofile_globals')->statement('ALTER TABLE `global_hero_draft_order_summary` PARTITION BY RANGE (`game_version`) (PARTITION p_270 VALUES LESS THAN (271), PARTITION p_future VALUES LESS THAN MAXVALUE)');
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_draft_order_summary');
    }
};
