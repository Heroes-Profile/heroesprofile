<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Endpoint registry for local databases. Rows are wide here for readability and
 * expanded into per-plan quota rows on insert.
 *
 * endpoint_id is not referenced anywhere, so these need not match production ids.
 */
class ApiEndpointRegistrySeeder extends Seeder
{
    private const CONNECTION = 'heroesprofile_api';

    /** basic, intermediate, developer, partner, ngs, heroes_lounge. */
    private const PLAN_IDS = [1, 2, 3, 4, 5, 6];

    /** [endpoint, name, group_name, group_sort, sort, ...one allowance per PLAN_IDS] */
    private const ENDPOINTS = [
        ['activity_unique_players', 'Tools/Activity/UniquePlayers', 'Tools', 9, 2, 70, 210, 1000, 10000, 0, 0],
        ['compare', 'Compare', 'Player Miscellaneous', 6, 2, 25, 500, 5000, 50000, 0, 0],
        ['global_compositions', 'Global/Compositions', 'Hero Data', 3, 8, 70, 210, 1000, 10000, 0, 0],
        ['global_compositions_heroes', 'Global/Compositions/Heroes', 'Hero Data', 3, 9, 70, 210, 1000, 10000, 0, 0],
        ['global_draft', 'Global/Draft', 'Hero Data', 3, 10, 70, 210, 1000, 10000, 0, 0],
        ['global_party', 'Global/Party', 'Hero Data', 3, 11, 70, 210, 1000, 10000, 0, 0],
        ['heroes', 'Heroes', 'General', 1, 2, 1000000, 1000000, 1000000, 1000000, 0, 0],
        ['heroes_map_stats', 'Heroes/Map/Stats', 'Hero Data', 3, 6, 70, 210, 1000, 10000, 0, 0],
        ['heroes_matchups', 'Hero/Matchups', 'Hero Data', 3, 2, 700, 2100, 10000, 100000, 0, 0],
        ['heroes_matchups_talents', 'Heroes/Matchups/Talents', 'Hero Data', 3, 7, 700, 2100, 10000, 100000, 0, 0],
        ['heroes_stats', 'Heroes/Stats', 'Hero Data', 3, 1, 70, 210, 1000, 10000, 0, 0],
        ['heroes_talents', 'Heroes/Talents', 'General', 1, 3, 1000000, 1000000, 1000000, 1000000, 0, 0],
        ['heroes_talents_builds', 'Heroes/Talents/Builds', 'Hero Data', 3, 4, 7, 21, 100, 1000, 0, 0],
        ['heroes_talents_builds_all', 'Heroes/Talents/Builds/All', 'Hero Data', 3, 5, 7, 21, 100, 1000, 0, 0],
        ['heroes_talents_details', 'Heroes/Talents/Details', 'Hero Data', 3, 3, 70, 210, 1000, 10000, 0, 0],
        ['leaderboard', 'Leaderboard', 'General', 1, 8, 70, 210, 1000, 10000, 0, 0],
        ['maps', 'Maps', 'General', 1, 4, 1000000, 1000000, 1000000, 1000000, 0, 0],
        ['mmr_tier', 'MMR/Tier', 'General', 1, 5, 1000000, 1000000, 1000000, 1000000, 0, 0],
        ['ngs_division_single', 'NGS/Division/Single', 'NGS Public Data', 7, 10, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_divisions', 'NGS/Divisions', 'NGS Public Data', 7, 8, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_games_upload', 'NGS/Games/Upload', 'NGS Replay Upload', 8, 1, 0, 0, 0, 0, 100000, 0],
        ['ngs_hero_stat', 'NGS/Hero/Stat', 'NGS Public Data', 7, 3, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_leaderboard_highest_average_stat', 'NGS/Leaderboard/Highest/Average/Stat', 'NGS Public Data', 7, 1, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_leaderboard_highest_total_stat', 'NGS/Leaderboard/Highest/Total/Stat', 'NGS Public Data', 7, 2, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_match', 'NGS/Match', 'NGS Public Data', 7, 6, 100, 10000, 25000, 50000, 50000, 0],
        ['ngs_player_profile', 'NGS/Player/Profile', 'NGS Public Data', 7, 4, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_single_player', 'NGS/Single/Player', 'NGS Public Data', 7, 12, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_single_team', 'NGS/Single/Team', 'NGS Public Data', 7, 11, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_standings', 'NGS/Standings', 'NGS Public Data', 7, 7, 50, 100, 1000, 10000, 1000000, 0],
        ['ngs_team_match_history', 'NGS/Team/Match/History', 'NGS Public Data', 7, 13, 100, 10000, 25000, 50000, 50000, 0],
        ['ngs_teams', 'NGS/Teams', 'NGS Public Data', 7, 9, 50, 100, 1000, 10000, 1000000, 0],
        ['randomize_me', 'Tools/RandomizeMe', 'Tools', 9, 1, 1000000, 1000000, 1000000, 1000000, 0, 0],
        ['patches', 'Patches', 'General', 1, 1, 1000000, 1000000, 1000000, 1000000, 0, 0],
        ['player', 'Player', 'Player Data', 4, 1, 10000, 25000, 50000, 250000, 250000, 250000],
        ['player_friendfoe', 'Player/FriendFoe', 'Player Data', 4, 13, 25, 500, 5000, 50000, 0, 0],
        ['player_hero_all', 'Player/Hero/All', 'Player Data', 4, 4, 25, 500, 5000, 50000, 0, 0],
        ['player_hero_single', 'Player/Hero/Single', 'Player Data', 4, 5, 25, 500, 5000, 50000, 0, 0],
        ['player_map_all', 'Player/Map/All', 'Player Data', 4, 9, 25, 500, 5000, 50000, 0, 0],
        ['player_map_single', 'Player/Map/Single', 'Player Data', 4, 10, 25, 500, 5000, 50000, 0, 0],
        ['player_match_history', 'Player/Match/History', 'Player Data', 4, 11, 25, 500, 5000, 50000, 0, 0],
        ['player_matchups', 'Player/Matchups', 'Player Data', 4, 12, 25, 500, 5000, 50000, 0, 0],
        ['player_mmr', 'Player/MMR', 'Player MMR Data', 5, 1, 10000, 25000, 50000, 250000, 100000, 100000],
        ['player_mmr_hero', 'Player/MMR/Hero', 'Player MMR Data', 5, 2, 10000, 25000, 50000, 250000, 100000, 100000],
        ['player_mmr_role', 'Player/MMR/Role', 'Player MMR Data', 5, 3, 10000, 25000, 50000, 250000, 100000, 100000],
        ['player_prematch', 'Player/PreMatch', 'Player Miscellaneous', 6, 1, 1000, 10000, 25000, 100000, 0, 0],
        // Generous on every plan, NGS and Heroes Lounge included: polling this is
        // an obligation the terms impose, so no tier should be rate-limited out of
        // complying with it.
        ['player_privacy_changes', 'Player/Privacy/Changes', 'Player Data', 4, 14, 10080, 10080, 10080, 10080, 10080, 10080],
        ['player_replays', 'Player/Replays', 'Player Data', 4, 2, 25, 500, 5000, 50000, 0, 0],
        ['player_role_all', 'Player/Role/All', 'Player Data', 4, 7, 25, 500, 5000, 50000, 0, 0],
        ['player_role_single', 'Player/Role/Single', 'Player Data', 4, 8, 25, 500, 5000, 50000, 0, 0],
        ['player_talents_build', 'Player/Talents/Build', 'Player Data', 4, 6, 25, 500, 5000, 50000, 0, 0],
        // Sized against the archive rather than by tier convention: at 1000 rows a
        // call, 60,000 calls is every replay there is. So these read as a share of
        // the whole per week — Basic enough to build against, Intermediate 8%,
        // Developer a third, Partner one full pass.
        ['replay_index', 'Replays', 'Replays', 2, 4, 100, 5000, 20000, 60000, 0, 0],
        // The three per-replay reads carry the same allowance. Which slice of a
        // replay a caller wants — all of it, the bans, the draft — is their
        // business, and pricing the slices differently only decides which endpoint
        // they contort their integration around.
        ['replay_ban', 'Replay/Ban', 'Replays', 2, 3, 1000, 25000, 250000, 1000000, 0, 0],
        ['replay_draft', 'Replay/Draft', 'Replays', 2, 5, 1000, 25000, 250000, 1000000, 0, 0],
        ['replay_data', 'Replay/Data', 'Replays', 2, 2, 1000, 25000, 250000, 1000000, 0, 0],
        ['replay_download', 'Replay/Download', 'Replays', 2, 1, 1000, 10000, 25000, 100000, 0, 0],
        ['talent_builder', 'Heroes/Talents/Builder', 'Hero Data', 3, 12, 7, 21, 100, 1000, 0, 0],
        ['talent_builder_replays', 'Heroes/Talents/Builder/Replays', 'Hero Data', 3, 13, 7, 21, 100, 1000, 0, 0],
    ];

    public function run(): void
    {
        $connection = DB::connection(self::CONNECTION);

        if ($connection->table('api_endpoints')->exists()) {
            $this->command?->info('ApiEndpointRegistrySeeder: registry already populated, skipping.');

            return;
        }

        $now = now();
        $endpoints = [];
        $quotas = [];

        foreach (self::ENDPOINTS as $index => $row) {
            [$endpoint, $name, $groupName, $groupSort, $sort] = $row;
            $endpointId = $index + 1;

            $endpoints[] = [
                'endpoint_id' => $endpointId,
                'endpoint' => $endpoint,
                'name' => $name,
                'group_name' => $groupName,
                'group_sort' => $groupSort,
                'sort' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach (self::PLAN_IDS as $offset => $planId) {
                $quotas[] = [
                    'endpoint_id' => $endpointId,
                    'subscription_plan' => $planId,
                    'calls_per_week' => $row[5 + $offset],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $connection->table('api_endpoints')->insert($endpoints);

        foreach (array_chunk($quotas, 200) as $chunk) {
            $connection->table('api_endpoint_quotas')->insert($chunk);
        }

        $this->command?->info(sprintf(
            'ApiEndpointRegistrySeeder: %d endpoints, %d quota rows.',
            count($endpoints),
            count($quotas)
        ));
    }
}
