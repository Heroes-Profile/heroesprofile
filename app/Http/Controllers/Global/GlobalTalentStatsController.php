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
use App\Support\GlobalCacheFreshness;
use App\Support\GlobalCacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalTalentStatsController extends GlobalsInputValidationController
{
    use HandlesAsyncGlobalQueries;

    public const MINIMUM_PATCH = '2.50.0.79155';

    // Bump when the cached data changes shape or maths, so old entries are skipped.
    private const TALENT_STATS_CACHE_VERSION = 1;

    private const BUILDS_CACHE_VERSION = 2;

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

        $defaultTimeframeType = $this->globalDataService->getDefaultTimeframeType();
        $defaultTimeframe = $this->globalDataService->getDefaultTimeframe();
        $gameTypeDefault = $this->globalDataService->getGameTypeDefault('multi');
        $defaultBuildType = $this->globalDataService->getDefaultBuildType();

        $initialTalentData = null;
        $initialBuildData = null;

        // Default view only: anything in the query string means the page asks for something else.
        if ($userinput && empty($request->query())) {
            $parameters = [
                'hero' => $userinput->name,
                'timeframe_type' => $defaultTimeframeType,
                'timeframe' => [$defaultTimeframe],
                'game_type' => $gameTypeDefault,
                'statfilter' => 'win_rate',
                'mirror' => '0',
            ];
            $buildParameters = array_merge($parameters, ['talentbuildtype' => $defaultBuildType]);

            $gameVersion = $this->globalDataService->getTimeframeFilterValues($defaultTimeframeType, [$defaultTimeframe]);
            $versionIds = $this->gameVersionIds($gameVersion);

            $initialTalentData = $this->cachedGlobalResult(
                $this->globalCacheKey('GlobalHeroTalentStats', $versionIds, $parameters, self::TALENT_STATS_CACHE_VERSION),
                $gameVersion,
                'executeGlobalHeroTalentData',
                $parameters
            );
            $initialBuildData = $this->cachedGlobalResult(
                $this->globalCacheKey('GlobalHeroTalentStatsBuilds', $versionIds, $buildParameters, self::BUILDS_CACHE_VERSION),
                $gameVersion,
                'executeGlobalHeroTalentBuildData',
                $buildParameters
            );
        }

        return view('Global.Talents.globalTalentStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(true, self::MINIMUM_PATCH),
                'gametypedefault' => $gameTypeDefault,
                'defaulttimeframetype' => $defaultTimeframeType,
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframe' => [$defaultTimeframe],
                'defaultbuildtype' => $defaultBuildType,
                'talentStatsLayout' => $this->globalDataService->getTalentStatsLayout(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'urlparameters' => $request->all(),
                'initialtalentdata' => $initialTalentData,
                'initialbuilddata' => $initialBuildData,
                'topbuild' => $initialBuildData ? collect($initialBuildData)->first() : null,
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

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);

        $cacheKey = $this->globalCacheKey('GlobalHeroTalentStats', SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray(), $request->all(), self::TALENT_STATS_CACHE_VERSION);

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeGlobalHeroTalentData');
    }

    public function executeGlobalHeroTalentData(Request $request)
    {
        $hero = $this->globalDataService->getHeroFilterValue($request['hero']);

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);

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

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);

        $cacheKey = $this->globalCacheKey('GlobalHeroTalentStatsBuilds', SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray(), $request->all(), self::BUILDS_CACHE_VERSION);

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeGlobalHeroTalentBuildData');
    }

    public function getGlobalHeroTalentBuildDataAll(Request $request)
    {
        $heroes = $this->globalDataService->getHeroes();
        $cache = Cache::store('database');
        $talentbuildType = $this->globalDataService->getDefaultBuildType();
        $timeframeType = $this->globalDataService->getDefaultTimeframeType();
        $timeframe = [$this->globalDataService->getDefaultTimeframe()];

        // Top build only: the page shows nothing else. Renamed so the full 7-build payload isn't served from cache.
        $allCacheKey = GlobalCacheKey::prefix('GlobalHeroTalentStatsBuildsAllTop', self::BUILDS_CACHE_VERSION).'|'.hash('sha256', json_encode([
            'timeframe_type' => $timeframeType,
            'timeframe' => $timeframe,
            'talentbuildtype' => $talentbuildType,
        ]));

        $cachedAll = $cache->get($allCacheKey);
        if ($cachedAll !== null) {
            return $cachedAll;
        }

        $gameTypes = ['qm', 'sl', 'ar'];
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($timeframeType, $timeframe);
        // The same for every hero and game type: one lookup rather than ~270.
        $versionIds = $this->gameVersionIds($gameVersion);
        $window = $this->globalDataService->calculateCacheWindow($gameVersion);

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

                $cacheKey = $this->globalCacheKey('GlobalHeroTalentStatsBuilds', $versionIds, $heroRequest->all(), self::BUILDS_CACHE_VERSION);

                $cached = $cache->get($cacheKey);
                $result[$heroModel->name][$gameType] = $cached === null ? null : collect($cached)->take(1)->values();

                // Stale is served like a hit but refreshed like a miss, and keeps the
                // combined result uncached until the refresh lands.
                if ($cached === null || GlobalCacheFreshness::isStale($cacheKey, $window)) {
                    $hasMisses = true;
                    app(GlobalQueryService::class)->dispatchIfNotPending(
                        $cacheKey,
                        static::class,
                        'executeGlobalHeroTalentBuildData',
                        $heroRequest->all(),
                        $window->ttl
                    );
                }
            }
        }

        // Only as long as its children stay fresh, so it is rebuilt from their refreshes.
        if (! $hasMisses) {
            $cache->put($allCacheKey, $result, $window->fresh ?? $window->ttl);
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

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);

        // The same for every hero. Hoisted so this is one query rather than ninety.
        $versionIds = $this->gameVersionIds($gameVersion);

        $children = [];

        foreach ($this->globalDataService->getHeroes() as $heroModel) {
            $childRequest = $this->talentBuildChildRequest($request, $heroModel->name);

            $children[$heroModel->name] = [
                'cache_key' => $this->globalCacheKey('GlobalHeroTalentStatsBuilds', $versionIds, $childRequest, self::BUILDS_CACHE_VERSION),
                'request' => $childRequest,
            ];
        }

        // Built like every other global key: transport parameters (api_token, mode) are
        // ignored, and the version ids are part of it so a new patch gets a new batch.
        return app(GlobalQueryService::class)->dispatchBatch(
            GlobalCacheKey::for('GlobalHeroTalentStatsBuildsAllFiltered', $versionIds, $request->all(), self::BUILDS_CACHE_VERSION),
            $children,
            static::class,
            'executeGlobalHeroTalentBuildData',
            $this->globalDataService->calculateCacheWindow($gameVersion),
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

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);

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
            $item->games_played = (int) round($gamesPlayed);
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
            ->excludeMirror($mirror)
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
            ->excludeMirror($mirror)
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
            ->excludeMirror($mirror)
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
            ->excludeMirror($mirror)
            ->filterByRegion($region)
            // Every game on each build's level 1-10 path: the pick shares at 13, 16 and 20 need the other choices too.
            ->where(function ($outerQuery) use ($builds) {
                foreach ($builds->unique(fn ($build) => $build->level_one.'-'.$build->level_four.'-'.$build->level_seven.'-'.$build->level_ten) as $build) {
                    $outerQuery->orWhere(function ($q) use ($build) {
                        $q->where('level_one', $build->level_one)
                            ->where('level_four', $build->level_four)
                            ->where('level_seven', $build->level_seven)
                            ->where('level_ten', $build->level_ten);
                    });
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

        // A game that ended before level 20 counts towards a build by the share of players on its
        // path who went on to pick that build's remaining talents, so it adds up to one game across
        // every build it could have become rather than one game in each.
        $buildDataMap = [];
        foreach ($builds as $build) {
            $fullBuildKey = $build->level_one.'-'.$build->level_four.'-'.$build->level_seven.'-'.
                            $build->level_ten.'-'.$build->level_thirteen.'-'.$build->level_sixteen.'-'.
                            $build->level_twenty;

            $pathRows = $query->filter(fn ($row) => $row->level_one == $build->level_one &&
                $row->level_four == $build->level_four &&
                $row->level_seven == $build->level_seven &&
                $row->level_ten == $build->level_ten);

            $share = function ($picked, $reached) use ($pathRows) {
                $reachedGames = $pathRows->filter($reached)->sum('games_played');

                return $reachedGames > 0 ? $pathRows->filter($picked)->sum('games_played') / $reachedGames : 0;
            };

            $shareThirteen = $share(
                fn ($row) => $row->level_thirteen == $build->level_thirteen,
                fn ($row) => $row->level_thirteen != 0
            );
            $shareSixteen = $share(
                fn ($row) => $row->level_thirteen == $build->level_thirteen && $row->level_sixteen == $build->level_sixteen,
                fn ($row) => $row->level_thirteen == $build->level_thirteen && $row->level_sixteen != 0
            );
            $shareTwenty = $share(
                fn ($row) => $row->level_thirteen == $build->level_thirteen && $row->level_sixteen == $build->level_sixteen && $row->level_twenty == $build->level_twenty,
                fn ($row) => $row->level_thirteen == $build->level_thirteen && $row->level_sixteen == $build->level_sixteen && $row->level_twenty != 0
            );

            $buildDataMap[$fullBuildKey] = [
                'wins' => 0,
                'losses' => 0,
                'total_filter_type' => 0,
            ];

            foreach ($pathRows as $row) {
                if ($row->level_thirteen == 0) {
                    $weight = $shareThirteen * $shareSixteen * $shareTwenty;
                } elseif ($row->level_thirteen != $build->level_thirteen) {
                    continue;
                } elseif ($row->level_sixteen == 0) {
                    $weight = $shareSixteen * $shareTwenty;
                } elseif ($row->level_sixteen != $build->level_sixteen) {
                    continue;
                } elseif ($row->level_twenty == 0) {
                    $weight = $shareTwenty;
                } elseif ($row->level_twenty == $build->level_twenty) {
                    $weight = 1;
                } else {
                    continue;
                }

                $buildDataMap[$fullBuildKey]['wins'] += $row->win_loss == 1 ? $row->games_played * $weight : 0;
                $buildDataMap[$fullBuildKey]['losses'] += $row->win_loss == 0 ? $row->games_played * $weight : 0;
                $buildDataMap[$fullBuildKey]['total_filter_type'] += $statFilter !== 'win_rate' ? ($row->total_filter_type ?? 0) * $weight : 0;
            }
        }

        return $buildDataMap;
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
