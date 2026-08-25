<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Global\Concerns\HandlesAsyncGlobalQueries;
use App\Models\GameType;
use App\Models\GlobalHeroTalentDetails;
use App\Models\GlobalHeroTalents;
use App\Models\HeroesDataTalent;
use App\Models\SeasonGameVersion;
use App\Rules\HeroInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\TalentBuildTypeInputValidation;
use App\Services\GlobalQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalTalentStatsController extends GlobalsInputValidationController
{
    use HandlesAsyncGlobalQueries;

    public function show(Request $request, $hero = null)
    {
        $validationRules = $this->globalValidationRulesURLParam($request['timeframe_type'], $request['timeframe']);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'errors' => $validator->errors()->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        if (! is_null($hero)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation],
            ];

            $validator = Validator::make(['hero' => $hero], $validationRules);

            if ($validator->fails()) {
                if (config('app.env') === 'production') {
                    return \Redirect::to('/');
                } else {
                    return [
                        'data' => $request->all(),
                        'errors' => $validator->errors()->all(),
                        'status' => 'failure to validate inputs',
                    ];
                }
            }
        }

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Global.Talents.globalTalentStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'urlparameters' => $request->all(),
            ]);
    }

    public function getGlobalHeroTalentData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['required', new HeroInputValidation],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero = $this->globalDataService->getHeroFilterValue($request['hero']);

        if ($request['timeframe_type'] == 'last_update') {
            $gameVersion = $this->globalDataService->getTimeframeFilterValuesLastUpdate($hero);
        } else {
            $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        }

        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $statFilter = $this->normalizeStatFilter($request['statfilter'] ?? null);
        $mirror = $request['mirror'];

        $cacheKey = $this->globalCacheKey('GlobalHeroTalentStats', SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray(), $request->all());

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeGlobalHeroTalentData');
    }

    public function executeGlobalHeroTalentData(Request $request)
    {
        $hero = $this->globalDataService->getHeroFilterValue($request['hero']);

        if ($request['timeframe_type'] == 'last_update') {
            $gameVersion = $this->globalDataService->getTimeframeFilterValuesLastUpdate($hero);
        } else {
            $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        }

        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $statFilter = $this->normalizeStatFilter($request['statfilter'] ?? null);
        $mirror = $request['mirror'];

        $gameVersionIds = SeasonGameVersion::whereIn('game_version', $gameVersion)
            ->pluck('id')
            ->toArray();

        if (empty($gameVersionIds)) {
            return collect();
        }

        $data = GlobalHeroTalentDetails::query()
            ->join('heroesprofile.heroes as heroes', 'heroes.id', '=', 'global_hero_talents_details.hero')
            ->select('heroes.name', 'global_hero_talents_details.hero as id', 'global_hero_talents_details.win_loss', 'global_hero_talents_details.talent', 'global_hero_talents_details.level')
            ->selectRaw('SUM(global_hero_talents_details.games_played) as games_played')
            ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                $column = str_replace('`', '``', $statFilter);

                return $query->selectRaw("SUM(`global_hero_talents_details`.`{$column}`) as total_filter_type");
            })
            ->filterByGameVersion($gameVersionIds)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->excludeMirror($mirror)
            ->filterByRegion($region)
            ->groupBy('global_hero_talents_details.hero', 'global_hero_talents_details.win_loss', 'global_hero_talents_details.talent', 'global_hero_talents_details.level')
            ->with(['talentInfo' => function ($query) {
                $query->withAllStatuses();
            }])
            ->get()
            ->map(function ($item) {
                $array = $item->toArray();
                // Normalize talentInfo to camelCase (toArray() converts relations to snake_case)
                if (isset($array['talent_info']) && ! isset($array['talentInfo'])) {
                    $array['talentInfo'] = $array['talent_info'];
                    unset($array['talent_info']);
                }

                return $array;
            });

        $data = collect($data)->groupBy('level')->map(function ($levelGroup) {

            // Exclude talent 0 when calculating total games at this level
            $validTalents = $levelGroup->groupBy('talent')->filter(function ($talentGroup) {
                $firstItem = $talentGroup->first();

                return $firstItem['talent'] != 0;
            });

            // Calculate total games at this level by summing wins + losses for each valid talent (excluding talent 0)
            $totalGamesPlayed = $validTalents->map(function ($talentGroup) {
                $wins = $talentGroup->where('win_loss', 1)->sum('games_played');
                $losses = $talentGroup->where('win_loss', 0)->sum('games_played');

                return $wins + $losses;
            })->sum();

            return $levelGroup->groupBy('talent')->map(function ($talentGroup) use ($totalGamesPlayed) {
                $firstItem = $talentGroup->first();

                $wins = $talentGroup->where('win_loss', 1)->sum('games_played');
                $losses = $talentGroup->where('win_loss', 0)->sum('games_played');
                $gamesPlayed = $wins + $losses;
                // Handle both camelCase and snake_case for talentInfo
                $talentInfo = $firstItem['talentInfo'] ?? $firstItem['talent_info'] ?? null;

                $winRate = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0;
                $popularity = $totalGamesPlayed > 0 ? round(($gamesPlayed / $totalGamesPlayed) * 100, 2) : 0;

                $statFilterTotal = $talentGroup->sum('total_filter_type');

                if ($talentInfo && isset($talentInfo['hero_name']) && $talentInfo['hero_name'] == $firstItem['name']) {
                    return [
                        'name' => $firstItem['name'],
                        'hero_id' => $firstItem['id'],
                        'wins' => $wins,
                        'losses' => $losses,
                        'games_played' => $gamesPlayed,
                        'popularity' => $popularity,
                        'win_rate' => $winRate,
                        'level' => $firstItem['level'],
                        'sort' => isset($talentInfo['sort']) ? $talentInfo['sort'] : null,
                        'talentInfo' => $talentInfo,
                        'total_filter_type' => $gamesPlayed > 0 ? round($statFilterTotal / $gamesPlayed, 2) : 0,
                    ];
                }

            })->sortBy('sort')->filter()->values()->toArray();
        });

        return $data;
    }

    public function getGlobalHeroTalentBuildData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['required', new HeroInputValidation],
            'talentbuildtype' => ['required', new TalentBuildTypeInputValidation],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $heroModel = $this->globalDataService->getHeroModel($request['hero']);
        $hero = $heroModel->id;

        if ($request['timeframe_type'] == 'last_update') {
            $gameVersion = $this->globalDataService->getTimeframeFilterValuesLastUpdate($hero);
        } else {
            $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        }

        $gameTypeRecords = GameType::whereIn('short_name', $request['game_type'])->get();
        $gameType = $gameTypeRecords->pluck('type_id')->toArray();

        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $statFilter = $this->normalizeStatFilter($request['statfilter'] ?? null);
        $mirror = $request['mirror'];
        $talentbuildType = $request['talentbuildtype'];

        $cacheKey = $this->globalCacheKey('GlobalHeroTalentStatsBuilds', SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray(), $request->all());

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeGlobalHeroTalentBuildData');
    }

    public function getGlobalHeroTalentBuildDataAll(Request $request)
    {
        $heroes = $this->globalDataService->getHeroes();
        $cache = Cache::store('database');
        $talentbuildType = $this->globalDataService->getDefaultBuildType();
        $timeframeType = $this->globalDataService->getDefaultTimeframeType();
        $timeframe = [$this->globalDataService->getDefaultTimeframe()];

        $allCacheKey = 'GlobalHeroTalentStatsBuildsAll|'.hash('sha256', json_encode([
            'timeframe_type' => $timeframeType,
            'timeframe' => $timeframe,
            'talentbuildtype' => $talentbuildType,
        ]));

        $cachedAll = $cache->get($allCacheKey);
        if ($cachedAll !== null) {
            return $cachedAll;
        }

        $gameTypes = ['qm', 'sl', 'ar'];
        $gameVersion = $timeframeType === 'last_update'
            ? null
            : $this->globalDataService->getTimeframeFilterValues($timeframeType, $timeframe);

        $result = [];
        $hasMisses = false;

        foreach ($heroes as $heroModel) {
            $result[$heroModel->name] = [];

            foreach ($gameTypes as $gameType) {
                $heroRequest = Request::create('/api/v1/global/talents/build', 'POST', [
                    'hero' => $heroModel->name,
                    'talentbuildtype' => $talentbuildType,
                    'timeframe_type' => $timeframeType,
                    'timeframe' => $timeframe,
                    'game_type' => [$gameType],
                    'region' => null,
                    'statfilter' => 'win_rate',
                    'hero_level' => null,
                    'game_map' => null,
                    'league_tier' => null,
                    'hero_league_tier' => null,
                    'role_league_tier' => null,
                    'mirror' => '0',
                ]);

                $resolvedGameVersion = $timeframeType === 'last_update'
                    ? $this->globalDataService->getTimeframeFilterValuesLastUpdate($heroModel->id)
                    : $gameVersion;

                $cacheKey = $this->globalCacheKey('GlobalHeroTalentStatsBuilds', SeasonGameVersion::select('id')->whereIn('game_version', $resolvedGameVersion)->pluck('id')->toArray(), $heroRequest->all());

                $cached = $cache->get($cacheKey);

                if ($cached !== null) {
                    $result[$heroModel->name][$gameType] = $cached;
                } else {
                    $hasMisses = true;
                    $result[$heroModel->name][$gameType] = null;
                    app(GlobalQueryService::class)->dispatchIfNotPending(
                        $cacheKey,
                        static::class,
                        'executeGlobalHeroTalentBuildData',
                        $heroRequest->all(),
                        $this->globalDataService->calculateCacheTimeInSeconds($resolvedGameVersion)
                    );
                }
            }
        }

        if (! $hasMisses) {
            $cacheTtlSeconds = $this->globalDataService->calculateCacheTimeInSeconds(
                $gameVersion ?? $this->globalDataService->getTimeframeFilterValues($timeframeType, $timeframe)
            );
            $cache->put($allCacheKey, $result, $cacheTtlSeconds);
        }

        return $result;
    }

    /**
     * Every hero's builds under one set of filters, answered as a single job.
     *
     * Separate from `getGlobalHeroTalentBuildDataAll()` above, which the site's own
     * page uses and which is deliberately untouched: that one takes no filters,
     * groups by game type, and answers 200 with nulls for whatever is still being
     * computed. This is the public API's version — the caller's filters, one entry
     * per hero, one job id to poll.
     *
     * Nothing here is grouped by game type. `filterByGameType` is a `whereIn`, so
     * asking for `sl,qm` is one query over both, not two results to nest.
     */
    public function getGlobalHeroTalentBuildDataAllFiltered(Request $request)
    {
        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'talentbuildtype' => ['required', new TalentBuildTypeInputValidation],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $lastUpdate = $request['timeframe_type'] == 'last_update';

        $gameVersion = $lastUpdate
            ? null
            : $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);

        // Same for every hero unless the timeframe is `last_update`, which resolves
        // per hero. Hoisted so this is one query rather than ninety.
        $sharedVersionIds = $lastUpdate ? null : $this->gameVersionIds($gameVersion);

        $children = [];

        foreach ($this->globalDataService->getHeroes() as $heroModel) {
            $resolvedGameVersion = $lastUpdate
                ? $this->globalDataService->getTimeframeFilterValuesLastUpdate($heroModel->id)
                : $gameVersion;

            $versionIds = $sharedVersionIds ?? $this->gameVersionIds($resolvedGameVersion);
            $childRequest = $this->talentBuildChildRequest($request, $heroModel->name);

            $children[$heroModel->name] = [
                'cache_key' => $this->globalCacheKey('GlobalHeroTalentStatsBuilds', $versionIds, $childRequest),
                'request' => $childRequest,
            ];
        }

        $parentRequest = $request->all();
        ksort($parentRequest);

        return app(GlobalQueryService::class)->dispatchBatch(
            'GlobalHeroTalentStatsBuildsAllFiltered|'.hash('sha256', json_encode($parentRequest)),
            $children,
            static::class,
            'executeGlobalHeroTalentBuildData',
            $this->globalDataService->calculateCacheTimeInSeconds(
                $gameVersion ?? $this->globalDataService->getTimeframeFilterValues(
                    $this->globalDataService->getDefaultTimeframeType(),
                    [$this->globalDataService->getDefaultTimeframe()]
                )
            ),
        );
    }

    /**
     * One hero's slice of a batch: the same request `/Global/Talents` sends for a
     * single hero, with the hero swapped in.
     *
     * Built explicitly rather than by copying the request so the batch cannot smuggle
     * its own parameters into a key the single-hero endpoint also uses. Order and
     * absent values do not matter — `globalCacheKey()` normalises both — so a hero
     * already computed under these filters by the page, or by a previous batch,
     * costs this one nothing.
     *
     * @return array<string, mixed>
     */
    private function talentBuildChildRequest(Request $request, string $heroName): array
    {
        return [
            'hero' => $heroName,
            'timeframe_type' => $request['timeframe_type'],
            'timeframe' => $request['timeframe'],
            'region' => $request['region'],
            'statfilter' => $request['statfilter'],
            'hero_level' => $request['hero_level'],
            'game_type' => $request['game_type'],
            'game_map' => $request['game_map'],
            'league_tier' => $request['league_tier'],
            'hero_league_tier' => $request['hero_league_tier'],
            'role_league_tier' => $request['role_league_tier'],
            'mirror' => $request['mirror'],
            'talentbuildtype' => $request['talentbuildtype'],
            'total_builds' => $request['total_builds'],
        ];
    }

    /**
     * How many builds one request asked for. The site's pages send nothing and get
     * the default; `total_builds` is the public API's way of asking for another
     * number, as the old API's parameter of the same name was.
     */
    private function buildsToReturnFor(Request $request): int
    {
        $requested = $request['total_builds'] ?? null;

        if ($requested === null || $requested === '') {
            return self::DEFAULT_BUILDS_TO_RETURN;
        }

        return max(1, min((int) $requested, self::MAX_BUILDS_TO_RETURN));
    }

    /** @return array<int, int> */
    private function gameVersionIds(array $gameVersion): array
    {
        return SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray();
    }

    public function executeGlobalHeroTalentBuildData(Request $request)
    {
        $heroModel = $this->globalDataService->getHeroModel($request['hero']);
        $hero = $heroModel->id;

        if ($request['timeframe_type'] == 'last_update') {
            $gameVersion = $this->globalDataService->getTimeframeFilterValuesLastUpdate($hero);
        } else {
            $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        }

        $gameType = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $statFilter = $this->normalizeStatFilter($request['statfilter'] ?? null);
        $mirror = $request['mirror'];
        $talentbuildType = $request['talentbuildtype'];

        // Read off the request, not held on the controller: this runs inside a job
        // that resolves its own instance, so anything set before the job was
        // created is gone by the time the query gets here.
        $this->buildsToReturn = $this->buildsToReturnFor($request);

        $topBuilds = null;
        if ($talentbuildType == 'Popular') {
            $topBuilds = $this->topBuildsOnPopularity($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region);
        } elseif ($talentbuildType == 'HP Algorithm') {
            $topBuilds = $this->topBuildsOnHPAlgorithm($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region);
        } elseif (strpos($talentbuildType, 'Unique') !== false) {
            preg_match('/\d+/', $talentbuildType, $matches);
            $uniqueLevel = $matches[0];
            $topBuilds = $this->topBuildsOnUniqueLevel($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $uniqueLevel);
        }

        // Fetch all build data in a single query
        $allBuildData = $this->getBatchTopBuildsData($topBuilds, $hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $statFilter);

        // Map the data back to each build
        foreach ($topBuilds as $build) {
            $buildKey = $build->level_one.'-'.$build->level_four.'-'.$build->level_seven.'-'.
                        $build->level_ten.'-'.$build->level_thirteen.'-'.$build->level_sixteen.'-'.
                        $build->level_twenty;
            $build->buildData = $allBuildData[$buildKey] ?? [
                'wins' => 0,
                'losses' => 0,
                'total_filter_type' => 0,
            ];
        }

        $talentData = HeroesDataTalent::withAllStatuses()
            ->where('hero_name', $heroModel->name)
            ->get();
        $talentData = $talentData->keyBy('talent_id');

        $sortBy = $statFilter == 'win_rate' ? 'win_rate' : 'total_filter_type';

        $topBuilds->transform(function ($item) use ($talentData, $heroModel) {
            // $item is an object (stdClass) from topBuilds methods
            $buildData = $item->buildData ?? ['wins' => 0, 'losses' => 0, 'total_filter_type' => 0];

            $wins = $buildData['wins'] ?? 0;
            $losses = $buildData['losses'] ?? 0;
            $gamesPlayed = $wins + $losses;
            $winRate = $gamesPlayed > 0 ? $wins / $gamesPlayed : 0;

            // Add win rate to the item (as object properties)
            $item->games_played = $gamesPlayed;
            $item->win_rate = round($winRate * 100, 2);
            $item->hero = $heroModel;
            $item->level_one = isset($talentData[$item->level_one]) ? $talentData[$item->level_one] : null;
            $item->level_four = isset($talentData[$item->level_four]) ? $talentData[$item->level_four] : null;
            $item->level_seven = isset($talentData[$item->level_seven]) ? $talentData[$item->level_seven] : null;
            $item->level_ten = isset($talentData[$item->level_ten]) ? $talentData[$item->level_ten] : null;
            $item->level_thirteen = isset($talentData[$item->level_thirteen]) ? $talentData[$item->level_thirteen] : null;
            $item->level_sixteen = isset($talentData[$item->level_sixteen]) ? $talentData[$item->level_sixteen] : null;
            $item->level_twenty = isset($talentData[$item->level_twenty]) ? $talentData[$item->level_twenty] : null;
            $item->total_filter_type = ($gamesPlayed > 0 ? round(($buildData['total_filter_type'] ?? 0) / $gamesPlayed, 2) : 0);

            return $item;
        });

        return $topBuilds->sortByDesc($sortBy)->values();
    }

    private function topBuildsOnPopularity($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region)
    {
        $gameVersionIds = SeasonGameVersion::whereIn('game_version', $gameVersion)
            ->pluck('id')
            ->toArray();

        if (empty($gameVersionIds)) {
            return collect();
        }

        $data = GlobalHeroTalents::query()
            ->join('heroesprofile_globals.talent_combinations as talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(global_hero_talents.games_played) AS games_played')
            ->filterByGameVersion($gameVersionIds)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->where('level_twenty', '!=', 0)
            ->groupBy('global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'hero' => $item->hero,
                    'level_one' => $item->level_one,
                    'level_four' => $item->level_four,
                    'level_seven' => $item->level_seven,
                    'level_ten' => $item->level_ten,
                    'level_thirteen' => $item->level_thirteen,
                    'level_sixteen' => $item->level_sixteen,
                    'level_twenty' => $item->level_twenty,
                    'games_played' => $item->games_played,
                ];
            })
            ->sortByDesc('games_played')
            ->take($this->buildsToReturn);

        return $data;
    }

    private function topBuildsOnHPAlgorithm($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region)
    {
        $gameVersionIds = SeasonGameVersion::whereIn('game_version', $gameVersion)
            ->pluck('id')
            ->toArray();

        if (empty($gameVersionIds)) {
            return collect();
        }

        $data = GlobalHeroTalents::query()
            ->join('heroesprofile_globals.talent_combinations as talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(global_hero_talents.games_played) AS games_played')
            ->filterByGameVersion($gameVersionIds)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->where('level_twenty', '!=', 0)
            ->groupBy('global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'hero' => $item->hero,
                    'level_one' => $item->level_one,
                    'level_four' => $item->level_four,
                    'level_seven' => $item->level_seven,
                    'level_ten' => $item->level_ten,
                    'level_thirteen' => $item->level_thirteen,
                    'level_sixteen' => $item->level_sixteen,
                    'level_twenty' => $item->level_twenty,
                    'games_played' => $item->games_played,
                ];
            });

        $uniqueRows = collect();
        $seenCombinations = [];

        foreach ($data->sortByDesc('games_played') as $row) {
            $combination = $row->level_one.'-'.$row->level_four.'-'.$row->level_seven;

            if (! isset($seenCombinations[$combination])) {
                $uniqueRows->push($row);
                $seenCombinations[$combination] = true;
            }
        }
        $sortedAndLimitedRows = $uniqueRows->sortByDesc('games_played')->take($this->buildsToReturn);

        return $sortedAndLimitedRows;
    }

    private function topBuildsOnUniqueLevel($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $uniqueLevel)
    {
        $levelMapping = [
            1 => 'level_one',
            4 => 'level_four',
            7 => 'level_seven',
            10 => 'level_ten',
            13 => 'level_thirteen',
            16 => 'level_sixteen',
            20 => 'level_twenty',
        ];

        $columnName = $levelMapping[$uniqueLevel];

        $gameVersionIds = SeasonGameVersion::whereIn('game_version', $gameVersion)
            ->pluck('id')
            ->toArray();

        if (empty($gameVersionIds)) {
            return collect();
        }

        $data = GlobalHeroTalents::query()
            ->join('heroesprofile_globals.talent_combinations as talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(global_hero_talents.games_played) AS games_played')
            ->filterByGameVersion($gameVersionIds)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->where('level_twenty', '!=', 0)
            ->groupBy('global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'hero' => $item->hero,
                    'level_one' => $item->level_one,
                    'level_four' => $item->level_four,
                    'level_seven' => $item->level_seven,
                    'level_ten' => $item->level_ten,
                    'level_thirteen' => $item->level_thirteen,
                    'level_sixteen' => $item->level_sixteen,
                    'level_twenty' => $item->level_twenty,
                    'games_played' => $item->games_played,
                ];
            });

        $filteredData = $data->sortByDesc('games_played')
            ->unique($columnName)
            ->take($this->buildsToReturn);

        return $filteredData;
    }

    private function getBatchTopBuildsData($builds, $hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $statFilter)
    {
        $statFilter = $this->normalizeStatFilter($statFilter);

        if ($builds->isEmpty()) {
            return [];
        }

        $gameVersionIds = SeasonGameVersion::whereIn('game_version', $gameVersion)
            ->pluck('id')
            ->toArray();

        if (empty($gameVersionIds)) {
            return collect();
        }

        $query = GlobalHeroTalents::query()
            ->join('heroesprofile_globals.talent_combinations as talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('global_hero_talents.win_loss', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(global_hero_talents.games_played) AS games_played')
            ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                $column = str_replace('`', '``', $statFilter);

                return $query->selectRaw("SUM(`global_hero_talents`.`{$column}`) as total_filter_type");
            })
            ->filterByGameVersion($gameVersionIds)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->where(function ($outerQuery) use ($builds) {
                foreach ($builds as $build) {
                    $buildLevels = [
                        ['thirteen' => 0, 'sixteen' => 0, 'twenty' => 0],
                        ['thirteen' => $build->level_thirteen, 'sixteen' => 0, 'twenty' => 0],
                        ['thirteen' => $build->level_thirteen, 'sixteen' => $build->level_sixteen, 'twenty' => 0],
                        ['thirteen' => $build->level_thirteen, 'sixteen' => $build->level_sixteen, 'twenty' => $build->level_twenty],
                    ];

                    foreach ($buildLevels as $levels) {
                        $outerQuery->orWhere(function ($q) use ($build, $levels) {
                            $q->where('level_one', $build->level_one)
                                ->where('level_four', $build->level_four)
                                ->where('level_seven', $build->level_seven)
                                ->where('level_ten', $build->level_ten)
                                ->where('level_thirteen', $levels['thirteen'])
                                ->where('level_sixteen', $levels['sixteen'])
                                ->where('level_twenty', $levels['twenty']);
                        });
                    }
                }
            })
            ->groupBy('global_hero_talents.win_loss', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'win_loss' => $item->win_loss,
                    'level_one' => $item->level_one,
                    'level_four' => $item->level_four,
                    'level_seven' => $item->level_seven,
                    'level_ten' => $item->level_ten,
                    'level_thirteen' => $item->level_thirteen,
                    'level_sixteen' => $item->level_sixteen,
                    'level_twenty' => $item->level_twenty,
                    'games_played' => $item->games_played,
                    'total_filter_type' => $item->total_filter_type ?? null,
                ];
            });

        // Group results by build key
        $buildDataMap = [];
        foreach ($query as $row) {
            // Match to the full build (not progressive levels)
            $buildKey = $row->level_one.'-'.$row->level_four.'-'.$row->level_seven.'-'.
                        $row->level_ten.'-'.$row->level_thirteen.'-'.$row->level_sixteen.'-'.
                        $row->level_twenty;

            // Find which original build this row belongs to by checking if it matches any progressive level
            foreach ($builds as $build) {
                $fullBuildKey = $build->level_one.'-'.$build->level_four.'-'.$build->level_seven.'-'.
                                $build->level_ten.'-'.$build->level_thirteen.'-'.$build->level_sixteen.'-'.
                                $build->level_twenty;

                // Check if this row matches this build's first 4 levels
                if ($row->level_one == $build->level_one &&
                    $row->level_four == $build->level_four &&
                    $row->level_seven == $build->level_seven &&
                    $row->level_ten == $build->level_ten) {

                    // Check if it matches any of the progressive levels
                    $matchesProgressiveLevel = (
                        ($row->level_thirteen == 0 && $row->level_sixteen == 0 && $row->level_twenty == 0) ||
                        ($row->level_thirteen == $build->level_thirteen && $row->level_sixteen == 0 && $row->level_twenty == 0) ||
                        ($row->level_thirteen == $build->level_thirteen && $row->level_sixteen == $build->level_sixteen && $row->level_twenty == 0) ||
                        ($row->level_thirteen == $build->level_thirteen && $row->level_sixteen == $build->level_sixteen && $row->level_twenty == $build->level_twenty)
                    );

                    if ($matchesProgressiveLevel) {
                        if (! isset($buildDataMap[$fullBuildKey])) {
                            $buildDataMap[$fullBuildKey] = [
                                'wins' => 0,
                                'losses' => 0,
                                'total_filter_type' => 0,
                            ];
                        }

                        $buildDataMap[$fullBuildKey]['wins'] += ($row->win_loss == 1 ? $row->games_played : 0);
                        $buildDataMap[$fullBuildKey]['losses'] += ($row->win_loss == 0 ? $row->games_played : 0);
                        $buildDataMap[$fullBuildKey]['total_filter_type'] += $statFilter !== 'win_rate' ? ($row->total_filter_type ?? 0) : 0;
                    }
                }
            }
        }

        // Round values
        foreach ($buildDataMap as $key => $data) {
            $buildDataMap[$key]['wins'] = round($data['wins']);
            $buildDataMap[$key]['losses'] = round($data['losses']);
        }

        return $buildDataMap;
    }

    private function getTopBuildsData($build, $win_loss, $hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $statFilter)
    {
        $statFilter = $this->normalizeStatFilter($statFilter);

        $buildStages = [
            ['thirteen' => 0, 'sixteen' => 0, 'twenty' => 0],      // Levels 1-10
            ['thirteen' => $build->level_thirteen, 'sixteen' => 0, 'twenty' => 0],  // Levels 1-13
            ['thirteen' => $build->level_thirteen, 'sixteen' => $build->level_sixteen, 'twenty' => 0],  // Levels 1-16
            ['thirteen' => $build->level_thirteen, 'sixteen' => $build->level_sixteen, 'twenty' => $build->level_twenty],  // Full build
        ];

        $transformedData = [
            'wins' => 0,
            'losses' => 0,
            'total_filter_type' => 0,
        ];

        $baseQuery = GlobalHeroTalents::query()
            ->join('heroesprofile_globals.talent_combinations as talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(games_played) AS games_played')
            ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                $column = str_replace('`', '``', $statFilter);

                return $query->selectRaw("SUM(`global_hero_talents`.`{$column}`) as total_filter_type");
            })
            ->filterByGameVersion($gameVersion)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->where('level_one', $build->level_one)
            ->where('level_four', $build->level_four)
            ->where('level_seven', $build->level_seven)
            ->where('level_ten', $build->level_ten)
            ->where(function ($query) use ($buildStages) {
                foreach ($buildStages as $stage) {
                    $query->orWhere(function ($q) use ($stage) {
                        $q->where('level_thirteen', $stage['thirteen'])
                            ->where('level_sixteen', $stage['sixteen'])
                            ->where('level_twenty', $stage['twenty']);
                    });
                }
            })
            ->groupBy('win_loss', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->get();

        foreach ($baseQuery as $row) {
            $wins = $row->win_loss == 1 ? $row->games_played : 0;
            $losses = $row->win_loss == 0 ? $row->games_played : 0;

            $transformedData['wins'] += $wins;
            $transformedData['losses'] += $losses;
            $transformedData['total_filter_type'] += $statFilter !== 'win_rate' ? ($row->total_filter_type ?? 0) : 0;
        }

        $transformedData['wins'] = round($transformedData['wins']);
        $transformedData['losses'] = round($transformedData['losses']);

        return $transformedData;
    }

    private function normalizeStatFilter($statFilter): string
    {
        if (! is_string($statFilter)) {
            return 'win_rate';
        }

        $statFilter = trim($statFilter);

        if ($statFilter === '') {
            return 'win_rate';
        }

        if (! in_array($statFilter, StatFilterInputValidation::VALID_STAT_CODES, true)) {
            return 'win_rate';
        }

        return $statFilter;
    }
}
