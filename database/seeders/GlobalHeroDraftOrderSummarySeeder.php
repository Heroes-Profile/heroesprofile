<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * global_hero_draft_order rolled up without hero level, hero rank, role rank and result,
 * which is what the Drafter reads when none of those filters are set. Built from the
 * seeded draft order rows, so it runs after GlobalHeroDraftOrderTableSeeder.
 */
class GlobalHeroDraftOrderSummarySeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('heroesprofile_globals')->statement('
            INSERT INTO global_hero_draft_order_summary (game_version, game_type, league_tier, game_map, region, hero, pick_number, count)
            SELECT game_version, game_type, league_tier, game_map, region, hero, pick_number, SUM(count)
            FROM global_hero_draft_order
            GROUP BY game_version, game_type, league_tier, game_map, region, hero, pick_number
        ');
    }
}
