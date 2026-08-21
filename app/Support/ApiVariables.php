<?php

namespace App\Support;

use App\Models\GameType;
use App\Models\Hero;
use App\Models\LeagueTier;
use App\Models\Map;
use App\Models\SeasonDate;
use App\Models\SeasonGameVersion;
use App\Rules\StackSizeInputValidation;
use App\Rules\TalentBuildTypeInputValidation;
use App\Services\Api\NgsLeaderboardService;

/**
 * What every parameter will and will not accept.
 *
 * Read from the same tables and rule classes the validators use, rather than
 * written out by hand — a list of hero names in prose is wrong the week a hero
 * ships, and a caller who trusts it gets a 422 with no way to tell why.
 */
class ApiVariables
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return [
            [
                'name' => 'hero',
                'used_by' => 'Most hero and player endpoints',
                'summary' => 'A hero name, exactly as spelled here. Always a name in this API, never an id — endpoints whose internals want an id translate for you.',
                'values' => Hero::orderBy('name')->pluck('name')->all(),
            ],
            [
                'name' => 'game_map',
                'also' => 'map',
                'used_by' => 'Global statistics, player breakdowns',
                'summary' => 'A map name. `players/maps/single` calls it `map`; everywhere else it is `game_map`.',
                'values' => Map::orderBy('name')->pluck('name')->all(),
            ],
            [
                'name' => 'game_type',
                'used_by' => 'Nearly everything',
                'summary' => 'Short names, comma-separated for endpoints that accept several. Defaults to `sl` where it is required and you omit it.',
                'pairs' => GameType::orderBy('type_id')->get()->mapWithKeys(
                    fn ($type) => [$type->short_name => $type->name]
                )->all(),
            ],
            [
                'name' => 'role',
                'used_by' => 'Player breakdowns, leaderboards, global filters',
                'summary' => 'A role name.',
                'values' => Hero::whereNotNull('new_role')->distinct()->orderBy('new_role')->pluck('new_role')->all(),
            ],
            [
                'name' => 'region',
                'used_by' => 'Player endpoints (required), global filters (optional)',
                'summary' => 'A numeric region id. Player endpoints require it; global endpoints treat its absence as every region.',
                'pairs' => ['1' => 'NA', '2' => 'EU', '3' => 'KR', '5' => 'CN'],
            ],
            [
                'name' => 'league_tier',
                'also' => 'hero_league_tier, role_league_tier, tierrank',
                'used_by' => 'Global statistics, leaderboards',
                'summary' => 'A tier id, not a name. All four parameters take the same set.',
                'pairs' => LeagueTier::orderBy('tier_id')->get()->mapWithKeys(
                    fn ($tier) => [(string) $tier->tier_id => $tier->tier_name ?? $tier->name ?? (string) $tier->tier_id]
                )->all(),
            ],
            [
                'name' => 'timeframe_type',
                'used_by' => 'Every global statistics endpoint',
                'summary' => 'How `timeframe` is read. `minor` is one build, `major` a patch line, `major_grouped` several patches together. `last_update` needs no `timeframe` at all.',
                'values' => ['minor', 'major', 'major_grouped', 'last_update'],
            ],
            [
                'name' => 'timeframe',
                'used_by' => 'Every global statistics endpoint',
                'summary' => 'A build (`2.55.17.97771`) when `timeframe_type` is `minor`, or a patch (`2.55`) when it is `major`. The most recent are listed; older ones remain valid.',
                'values' => SeasonGameVersion::orderByDesc('id')->limit(40)->pluck('game_version')->all(),
            ],
            [
                'name' => 'season',
                'used_by' => 'Leaderboards, player endpoints',
                'summary' => 'A season id. Omit it on player endpoints for a career total.',
                'values' => SeasonDate::orderByDesc('id')->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ],
            [
                'name' => 'groupsize',
                'used_by' => 'Leaderboards, party statistics, friend/foe',
                'summary' => 'Party size, by name rather than number.',
                'values' => array_keys((new StackSizeInputValidation)->allowed()),
            ],
            [
                'name' => 'talentbuildtype',
                'used_by' => 'heroes/talents/builds',
                'summary' => 'Which ranking decides the builds returned. Defaults to `Popular`.',
                'values' => (new TalentBuildTypeInputValidation)->allowed(),
            ],
            [
                'name' => 'stat',
                'used_by' => 'The two NGS leaderboards',
                'summary' => 'Which statistic to rank by. Checked against this list rather than trusted — the old API concatenated it straight into the query.',
                'values' => NgsLeaderboardService::STATS,
            ],
            [
                'name' => 'mirror',
                'used_by' => 'Global statistics',
                'summary' => 'Whether mirror matches are included.',
                'pairs' => ['0' => 'Exclude mirror matches', '1' => 'Include them'],
            ],
        ];
    }
}
