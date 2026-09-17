<?php

namespace App\Http\Controllers;

use App\Models\BannedAccount;
use App\Models\GameType;
use App\Models\LeagueTier;
use App\Models\Map;
use App\Models\MMRTypeID;
use App\Models\SeasonGameVersion;
use App\Rules\DateInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\RegionInputValidation;
use App\Rules\TimeframeMinorInputValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MatchSearchController extends Controller
{
    private const PAGE_SIZE = 1000;

    private const CHUNK_SIZE = 250000;

    private const TIME_BUDGET_SECONDS = 5;

    // Unranked Draft, Hero League and Team League have no games in the search window
    private const GAME_TYPES = ['qm', 'sl', 'ar'];

    public function show(Request $request)
    {
        $filters = $this->globalDataService->getFilterData();
        $filters->timeframes = $filters->timeframes->whereIn('code', $this->searchableGameVersions())->values();
        $filters->game_types_full = $filters->game_types_full->whereIn('code', self::GAME_TYPES)->values();

        return view('matchSearch')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'filters' => $filters,
            'gametypedefault' => self::GAME_TYPES,
            'oldestdate' => $this->searchWindowStart(),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
        ]);
    }

    public function getData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // A list, so the per-item rule below always applies; a plain string skipped it.
            'game_type' => ['required', 'array', new GameTypeInputValidation],
            'game_type.*' => 'in:'.implode(',', self::GAME_TYPES),
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'game_version' => ['sometimes', 'nullable', new TimeframeMinorInputValidation('minor')],
            'start_date' => ['sometimes', 'nullable', new DateInputValidation],
            'end_date' => ['sometimes', 'nullable', new DateInputValidation],
            'heroes' => 'sometimes|nullable|array|max:10',
            'heroes.*' => 'integer',
            'heroes_match' => 'sometimes|nullable|in:any,all',
            'player_mmr_min' => 'sometimes|nullable|integer',
            'player_mmr_max' => 'sometimes|nullable|integer',
            'hero_mmr_min' => 'sometimes|nullable|integer',
            'hero_mmr_max' => 'sometimes|nullable|integer',
            'role_mmr_min' => 'sometimes|nullable|integer',
            'role_mmr_max' => 'sometimes|nullable|integer',
            'player_rank' => 'sometimes|nullable|array',
            'player_rank.*' => 'integer',
            'hero_rank' => 'sometimes|nullable|array',
            'hero_rank.*' => 'integer',
            'role_rank' => 'sometimes|nullable|array',
            'role_rank.*' => 'integer',
            'players' => 'sometimes|nullable|array|max:10',
            'players.*.blizz_id' => 'required|integer',
            'players.*.region' => 'required|integer|in:1,2,3,5',
            'players_match' => 'sometimes|nullable|in:any,all',
            'before_replayID' => 'sometimes|nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $players = collect($request['players'] ?? []);

        // The picker never offers these, so this only catches a hand-built request
        if ($players->isNotEmpty() && $this->containsRestrictedPlayer($players)) {
            return [
                'data' => $request->all(),
                'errors' => ['One or more of those players cannot be searched for.'],
                'status' => 'failure to validate inputs',
            ];
        }

        // Walk replayIDs newest first in blocks, so a rare search returns what it has
        // after a few seconds instead of scanning two seasons in one query
        $floor = $this->windowStartReplayID();
        $upper = $request['before_replayID'] ? $request['before_replayID'] - 1 : (int) DB::table('replay')->max('replayID');
        $started = microtime(true);
        $replayIDs = [];
        $nextBefore = null;

        while ($upper >= $floor) {
            $lower = max($floor, $upper - self::CHUNK_SIZE + 1);
            $replayIDs = array_merge($replayIDs, $this->matchingReplayIDs($request, $players, $lower, $upper, self::PAGE_SIZE - count($replayIDs)));

            if (count($replayIDs) >= self::PAGE_SIZE) {
                $nextBefore = end($replayIDs);
                break;
            }

            $upper = $lower - 1;

            if ($upper >= $floor && microtime(true) - $started >= self::TIME_BUDGET_SECONDS) {
                $nextBefore = $lower;
                break;
            }
        }

        return [
            'data' => $this->describeReplays($replayIDs),
            // Send back as before_replayID to carry on; null once the window is fully searched
            'next_before_replayID' => $nextBefore,
            'page_full' => count($replayIDs) >= self::PAGE_SIZE,
            'searched_back_to' => $nextBefore ? DB::table('replay')->where('replayID', '>=', $nextBefore)->orderBy('replayID')->value('game_date') : null,
            'oldest_date' => $this->searchWindowStart(),
        ];
    }

    /**
     * Newest first. Every filter narrows; several heroes or players match a replay
     * containing any one of them.
     */
    private function matchingReplayIDs(Request $request, $players, int $lowerReplayID, int $upperReplayID, int $limit): array
    {
        $gameTypes = GameType::whereIn('short_name', (array) $request['game_type'])->pluck('type_id')->toArray();
        $gameMaps = $request['game_map'] ? Map::whereIn('name', (array) $request['game_map'])->pluck('map_id')->toArray() : null;
        $regions = $request['region'] ? $this->globalDataService->getRegionFilterValues((array) $request['region']) : null;
        $heroes = $request['heroes'];

        // HP MMR on the page is 1800 + 40 × the stored rating
        $ratingRanges = [];
        foreach (['player', 'hero', 'role'] as $type) {
            foreach (['min' => '>=', 'max' => '<='] as $bound => $operator) {
                $value = $request[$type.'_mmr_'.$bound];
                if (! is_null($value)) {
                    $ratingRanges[] = ['player.'.$type.'_conservative_rating', $operator, ($value - 1800) / 40];
                }
            }
        }

        return DB::table('replay')
            ->select('replay.replayID')
            // The replayID range is what bounds the primary key scan; game_date still
            // drops old games uploaded late, which carry high replayIDs
            ->whereBetween('replay.replayID', [$lowerReplayID, $upperReplayID])
            ->where('replay.game_date', '>=', $this->searchWindowStart())
            ->whereIn('replay.game_type', $gameTypes)
            ->when($gameMaps, fn ($query) => $query->whereIn('replay.game_map', $gameMaps))
            ->when($regions, fn ($query) => $query->whereIn('replay.region', $regions))
            ->when($request['game_version'], fn ($query) => $query->whereIn('replay.game_version', (array) $request['game_version']))
            ->when($request['start_date'], fn ($query) => $query->where('replay.game_date', '>=', $request['start_date']))
            ->when($request['end_date'], fn ($query) => $query->where('replay.game_date', '<', Carbon::parse($request['end_date'])->addDay()->toDateString()))
            // AND: a player on each chosen hero, each meeting every rating range and rank
            ->when($heroes && count($heroes) > 1 && $request['heroes_match'] === 'all', function ($query) use ($request, $heroes, $ratingRanges, $gameTypes) {
                foreach ($heroes as $hero) {
                    $query->whereExists(function ($subQuery) use ($request, $hero, $ratingRanges, $gameTypes) {
                        $this->playerConditions($subQuery, $request, [$hero], $ratingRanges, $gameTypes);
                    });
                }
            })
            // OR: one player, on any chosen hero if there are some, meeting every rating range and rank
            ->when(! ($heroes && count($heroes) > 1 && $request['heroes_match'] === 'all')
                && ($heroes || $ratingRanges || $request['player_rank'] || $request['hero_rank'] || $request['role_rank']), function ($query) use ($request, $heroes, $ratingRanges, $gameTypes) {
                    $query->whereExists(function ($subQuery) use ($request, $heroes, $ratingRanges, $gameTypes) {
                        $this->playerConditions($subQuery, $request, $heroes, $ratingRanges, $gameTypes);
                    });
                })
            // AND: a separate check per player, all of which must pass
            ->when($players->isNotEmpty() && $request['players_match'] === 'all', function ($query) use ($players) {
                foreach ($players as $player) {
                    $query->whereExists(function ($subQuery) use ($player) {
                        $subQuery->select(DB::raw(1))
                            ->from('player')
                            ->whereColumn('player.replayID', 'replay.replayID')
                            ->where('player.blizz_id', $player['blizz_id'])
                            ->where('replay.region', $player['region']);
                    });
                }
            })
            // OR: one check that any of them played
            ->when($players->isNotEmpty() && $request['players_match'] !== 'all', function ($query) use ($players) {
                $query->whereExists(function ($subQuery) use ($players) {
                    $subQuery->select(DB::raw(1))
                        ->from('player')
                        ->whereColumn('player.replayID', 'replay.replayID')
                        ->where(function ($q) use ($players) {
                            foreach ($players as $player) {
                                $q->orWhere(function ($inner) use ($player) {
                                    $inner->where('player.blizz_id', $player['blizz_id'])
                                        ->where('replay.region', $player['region']);
                                });
                            }
                        });
                });
            })
            ->orderByDesc('replay.replayID')
            ->limit($limit)
            ->pluck('replay.replayID')
            ->all();
    }

    /**
     * Lowest replayID among games in the search window, so the scan can stop there.
     *
     * Taken from the window's first week rather than all of it: a game played later
     * is uploaded later, after that week's uploads, so its replayID is higher anyway.
     * Reading the whole window would cost millions of index entries.
     */
    private function windowStartReplayID(): int
    {
        $windowStart = $this->searchWindowStart();

        return (int) Cache::remember('match_search_window_start_replay|'.$windowStart, 86400, function () use ($windowStart) {
            return DB::table('replay')
                ->where('game_date', '>=', $windowStart)
                ->where('game_date', '<', Carbon::parse($windowStart)->addDays(7)->toDateString())
                ->min('replayID') ?? 0;
        });
    }

    /**
     * One player row in the replay: on one of `$heroes` (if given), within every rating range and rank.
     */
    private function playerConditions($subQuery, Request $request, ?array $heroes, array $ratingRanges, array $gameTypes): void
    {
        $subQuery->select(DB::raw(1))
            ->from('player')
            ->whereColumn('player.replayID', 'replay.replayID')
            ->when($heroes, fn ($q) => $q->whereIn('player.hero', $heroes))
            ->where($ratingRanges)
            ->when($request['player_rank'], function ($q) use ($request, $gameTypes) {
                $this->whereRankTier($q, 'player.player_conservative_rating', $request['player_rank'], $gameTypes, [10000 => null]);
            })
            // Hero boundaries are per hero, so only the chosen heroes are looked up
            ->when($request['hero_rank'] && $heroes, function ($q) use ($request, $gameTypes, $heroes) {
                $this->whereRankTier($q, 'player.hero_conservative_rating', $request['hero_rank'], $gameTypes,
                    collect($heroes)->mapWithKeys(fn ($hero) => [$hero => ['player.hero', $hero]])->all());
            })
            ->when($request['role_rank'], function ($q) use ($request, $gameTypes) {
                $q->join('heroes', 'heroes.id', '=', 'player.hero');
                $roleTypes = $this->globalDataService->getHeroes()->pluck('new_role')->unique()
                    ->mapWithKeys(fn ($role) => [MMRTypeID::filterByName($role)->value('mmr_type_id') => ['heroes.new_role', $role]])
                    ->all();
                $this->whereRankTier($q, 'player.role_conservative_rating', $request['role_rank'], $gameTypes, $roleTypes);
            });
    }

    /**
     * Rating within any chosen tier, using that game type's current boundaries.
     *
     * @param  array  $mmrTypes  league_breakdowns type => [column, value] that selects the rows it applies to, or null for every row
     */
    private function whereRankTier($query, string $ratingColumn, array $tierIds, array $gameTypes, array $mmrTypes): void
    {
        $tierNames = LeagueTier::whereIn('tier_id', $tierIds)->pluck('name')->map(fn ($name) => strtolower($name));

        $query->where(function ($anyTier) use ($ratingColumn, $tierNames, $gameTypes, $mmrTypes) {
            $matched = false;

            foreach ($gameTypes as $gameType) {
                foreach ($mmrTypes as $mmrType => $scope) {
                    $tiers = $this->globalDataService->getRankTiers($gameType, $mmrType);

                    foreach ($tierNames as $tierName) {
                        if (! isset($tiers[$tierName])) {
                            continue;
                        }

                        $min = $tiers[$tierName]['min_mmr'];
                        $max = $tiers[$tierName]['max_mmr'];
                        $matched = true;

                        $anyTier->orWhere(function ($range) use ($ratingColumn, $gameType, $scope, $min, $max) {
                            $range->where('replay.game_type', $gameType)
                                ->when($scope, fn ($q) => $q->where($scope[0], $scope[1]))
                                // HP MMR is 1800 + 40 × the stored rating; Bronze has no floor, Master no ceiling
                                ->when($min > 0, fn ($q) => $q->where($ratingColumn, '>=', ($min - 1800) / 40))
                                ->when($max !== '', fn ($q) => $q->where($ratingColumn, '<', ($max - 1800) / 40));
                        });
                    }
                }
            }

            // No boundaries found means nothing can be in the tier
            if (! $matched) {
                $anyTier->whereRaw('1 = 0');
            }
        });
    }

    private function describeReplays(array $replayIDs): array
    {
        if (empty($replayIDs)) {
            return [];
        }

        $gameTypes = $this->globalDataService->getGameTypeIDtoString();
        $maps = Map::all()->keyBy('map_id');
        $heroes = $this->globalDataService->getHeroes()->keyBy('id');
        $regions = $this->globalDataService->getRegionIDtoString();

        $players = DB::table('player')
            ->select('replayID', 'hero', 'team', 'winner')
            ->whereIn('replayID', $replayIDs)
            ->get()
            ->groupBy('replayID');

        return DB::table('replay')
            ->select('replayID', 'game_date', 'game_type', 'game_map', 'game_length', 'region', 'game_version')
            ->whereIn('replayID', $replayIDs)
            ->orderByDesc('replayID')
            ->get()
            ->map(function ($replay) use ($players, $gameTypes, $maps, $heroes, $regions) {
                $replayPlayers = $players[$replay->replayID] ?? collect();
                $winningTeam = $replayPlayers->firstWhere('winner', 1)?->team;

                return [
                    'replayID' => $replay->replayID,
                    'game_date' => $replay->game_date,
                    'game_type' => $gameTypes[$replay->game_type] ?? null,
                    'game_map' => $maps[$replay->game_map] ?? null,
                    'game_length' => max(0, $replay->game_length - 70),
                    'region' => $regions[$replay->region] ?? null,
                    'game_version' => $replay->game_version,
                    'winner' => $winningTeam,
                    'heroes' => [
                        $replayPlayers->where('team', 0)->map(fn ($player) => $heroes[$player->hero] ?? null)->filter()->values(),
                        $replayPlayers->where('team', 1)->map(fn ($player) => $heroes[$player->hero] ?? null)->filter()->values(),
                    ],
                ];
            })
            ->all();
    }

    private function containsRestrictedPlayer($players): bool
    {
        $privateAccounts = $this->globalDataService->getPrivateAccounts();
        $bannedAccounts = BannedAccount::get();

        return $players->contains(function ($player) use ($privateAccounts, $bannedAccounts) {
            $matches = fn ($account) => $account['blizz_id'] == $player['blizz_id'] && $account['region'] == $player['region'];

            return $privateAccounts->contains($matches) || $bannedAccounts->contains($matches);
        });
    }

    /**
     * Patches that were live at some point in the search window: everything added since
     * it opened, plus whichever patch was already live when it did.
     */
    private function searchableGameVersions(): array
    {
        $windowStart = $this->searchWindowStart();

        $liveAtStart = SeasonGameVersion::where('date_added', '<', $windowStart)
            ->orderByDesc('date_added')
            ->value('game_version');

        return SeasonGameVersion::where('date_added', '>=', $windowStart)
            ->pluck('game_version')
            ->when($liveAtStart, fn ($versions) => $versions->push($liveAtStart))
            ->all();
    }

    private function searchWindowStart(): string
    {
        $seasons = $this->globalDataService->getSeasonsData();

        return ($seasons[1] ?? $seasons[0])->start_date;
    }
}
