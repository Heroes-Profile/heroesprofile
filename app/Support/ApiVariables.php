<?php

namespace App\Support;

use App\Models\GameType;
use App\Models\Hero;
use App\Models\LeagueTier;
use App\Models\Map;
use App\Models\NGS\NGSTeam;
use App\Models\SeasonDate;
use App\Rules\StackSizeInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\TalentBuildTypeInputValidation;
use App\Services\GlobalDataService;

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
                'summary' => 'A map name, case-insensitive. `players/maps/single` calls it `map`; everywhere else it is `game_map`. Only playable maps are listed and accepted, except by `replays`, which also takes retired ones.',
                'values' => Map::where('playable', '<>', 0)->orderBy('name')->pluck('name')->all(),
            ],
            [
                'name' => 'game_type',
                'used_by' => 'Nearly everything',
                'summary' => 'Either form works — `Storm League` or `sl`, case-insensitive. Comma-separated for endpoints that accept several. Required by the global statistics endpoints. Player endpoints default to every type, except rating history, which defaults to `sl`. Leaderboards also default to `sl`.',
                'pairs' => GameType::whereIn('short_name', ApiParameters::GAME_TYPES)
                    ->orderBy('type_id')->get()->mapWithKeys(
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
                'summary' => 'Either form works — `NA` or `1`. Player endpoints require it; global endpoints treat its absence as every region.',
                'pairs' => ['1' => 'NA', '2' => 'EU', '3' => 'KR', '5' => 'CN'],
            ],
            [
                'name' => 'hero_level',
                'used_by' => 'Global statistics',
                'summary' => 'A band, not a level — and the value is the band\'s lower bound, so `25` means 25 to 40 rather than "25 or above". Levels are stored bucketed, so a value that is not one of these codes matches nothing. Comma-separated for several bands.',
                'pairs' => HeroLevelBands::all(),
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
                'summary' => 'How `timeframe` is read. `minor` is one build, `major` a patch line, `major_grouped` several patches together.',
                'values' => ['minor', 'major', 'major_grouped'],
            ],
            [
                'name' => 'timeframe',
                'used_by' => 'Every global statistics endpoint',
                'summary' => 'A build (`2.55.17.97771`) when `timeframe_type` is `minor`, or a patch (`2.55`) when it is `major`. This is every patch that can be queried: older data exists but is not offered, the same limit the site applies to its own filters.',
                'values' => app(GlobalDataService::class)->queryableGameVersions(),
            ],
            [
                'name' => 'season',
                'used_by' => 'Leaderboards, player endpoints',
                'summary' => 'A season id. Omit it on player endpoints for a career total.',
                'values' => SeasonDate::orderByDesc('id')->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ],
            [
                'name' => 'season (NGS)',
                'used_by' => 'NGS endpoints',
                'summary' => 'An NGS season number — not the same thing as a ranked season id. The NGS page endpoints default to the latest; team and player endpoints treat its absence as every season.',
                'values' => NGSTeam::distinct()->orderByDesc('season')->pluck('season')->map(fn ($season) => (string) $season)->all(),
            ],
            [
                'name' => 'division',
                'used_by' => 'NGS endpoints',
                'summary' => 'An NGS division, spelled exactly as here.',
                'values' => NGSTeam::distinct()->orderBy('division')->pluck('division')->all(),
            ],
            [
                'name' => 'groupsize',
                'used_by' => 'heroes/stats, leaderboards, players/friendfoe',
                'summary' => 'Party size, by name rather than number.',
                'values' => array_keys((new StackSizeInputValidation)->allowed()),
            ],
            [
                'name' => 'teamoneparty',
                'also' => 'teamtwoparty, ally_combo, enemy_combo',
                'used_by' => 'Party statistics',
                'summary' => 'A party composition code. Five digits, one per group size, read left to right as five-stack, quad, triple, double, solo — each digit being the number of *players* in groups of that size, so every code sums to five. `00023` is two players in a duo plus three solos. These are also the keys of the `/party` response and the values of its `ally_combo` and `enemy_combo` fields.',
                'pairs' => PartyCombinations::all(),
            ],
            [
                'name' => 'talentbuildtype',
                'used_by' => 'heroes/talents/builds',
                'summary' => 'Which ranking decides the builds returned. Defaults to `Popular`.',
                'values' => (new TalentBuildTypeInputValidation)->allowed(),
            ],
            [
                'name' => 'statfilter',
                'used_by' => 'heroes/stats, heroes/talents/details, heroes/talents/builds, heroes/talents/builds/all',
                'summary' => 'Which statistic to report. Defaults to `win_rate`. Anything else needs `timeframe_type=minor` and at most five timeframes.',
                'values' => StatFilterInputValidation::VALID_STAT_CODES,
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
