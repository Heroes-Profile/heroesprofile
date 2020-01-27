<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $this->call(announcer_seeder::class);
      $this->call(awards_seeder::class);
      $this->call(banners_seeder::class);
      $this->call(game_types_seeder::class);
      $this->call(global_hero_change_seeder::class);
      $this->call(global_hero_matchups_ally_seeder::class);
      $this->call(global_hero_matchups_enemy_seeder::class);
      $this->call(global_hero_stats_bans_seeder::class);
      $this->call(global_hero_stats_old_seeder::class);
      $this->call(global_hero_stats_seeder::class);
      $this->call(global_hero_talents_details_seeder::class);
      $this->call(global_hero_talents_seeder::class);
      $this->call(heroes_data_abilities_seeder::class);
      $this->call(heroes_data_talents_seeder::class);
      $this->call(heroes_seeder::class);
      $this->call(heroes_skins_seeder::class);
      $this->call(heroes_translations_seeder::class);
      $this->call(league_breakdowns_seeder::class);
      $this->call(league_tiers_seeder::class);
      $this->call(maps_seeder::class);
      $this->call(master_games_played_data_seeder::class);
      $this->call(master_mmr_data_seeder::class);
      $this->call(mmr_type_ids_seeder::class);
      $this->call(mmr_type_ids_seeder::class);
      $this->call(mounts_seeder::class);
      $this->call(player_seeder::class);
      $this->call(portraits_seeder::class);
      $this->call(replay_bans_seeder::class);
      $this->call(replay_experience_breakdown_seeder::class);
      $this->call(replay_seeder::class);
      $this->call(scores_seeder::class);
      $this->call(season_dates_seeder::class);
      $this->call(season_game_versions_seeder::class);
      $this->call(sprays_seeder::class);
      $this->call(talents_seeder::class);
      $this->call(talent_combinations_seeder::class);
      $this->call(voice_lines_seeder::class);

      //Cache
      $this->call(leaderboard_cache_seeder::class);
      $this->call(table_cache_value_seeder::class);

      //Brawls
      $this->call(player_brawl_seeder::class);
      $this->call(replay_brawl_seeder::class);
      $this->call(scores_brawl_seeder::class);
      $this->call(talents_brawl_seeder::class);
    }
}
