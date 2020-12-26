<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $this->call(awards_seeder::class);
      $this->call(battletags_seeder::class);
      $this->call(game_types_seeder::class);
      $this->call(global_hero_change_seeder::class);
      $this->call(global_hero_matchups_ally_seeder::class);
      $this->call(global_hero_matchups_enemy_seeder::class);
      $this->call(global_hero_stats_bans_seeder::class);
      $this->call(global_hero_stats_old_seeder::class);
      $this->call(global_hero_stats_seeder::class);
      $this->call(global_hero_talents_details_seeder::class);
      $this->call(global_hero_talents_seeder::class);
      $this->call(heroes_data_talents_seeder::class);
      $this->call(heroes_seeder::class);
      $this->call(league_breakdowns_seeder::class);
      $this->call(league_tiers_seeder::class);
      $this->call(maps_seeder::class);
      $this->call(master_mmr_data_ar_seeder::class);
      $this->call(master_mmr_data_hl_seeder::class);
      $this->call(master_mmr_data_qm_seeder::class);
      $this->call(master_mmr_data_sl_seeder::class);
      $this->call(master_mmr_data_tl_seeder::class);
      $this->call(master_mmr_data_ud_seeder::class);
      $this->call(mmr_type_ids_seeder::class);
      $this->call(player_seeder::class);
      $this->call(replay_bans_seeder::class);
      $this->call(replay_experience_breakdown_seeder::class);
      $this->call(replay_seeder::class);
      $this->call(scores_seeder::class);
      $this->call(season_dates_seeder::class);
      $this->call(season_game_versions_seeder::class);
      $this->call(talents_seeder::class);
      $this->call(talent_combinations_seeder::class);

      //Cache
      $this->call(leaderboard_cache_seeder::class);
      $this->call(table_cache_value_seeder::class);
    }
}
