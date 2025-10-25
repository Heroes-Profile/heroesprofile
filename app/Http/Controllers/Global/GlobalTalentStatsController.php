<?php

namespace App\Http\Controllers\Global;

use App\Models\GameType;
use App\Models\GlobalHeroTalentDetails;
use App\Models\GlobalHeroTalents;
use App\Models\HeroesDataTalent;
use App\Rules\HeroInputValidation;
use App\Rules\TalentBuildTypeInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalTalentStatsController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null)
    {
        $validationRules = $this->globalValidationRulesURLParam($request['timeframe_type'], $request['timeframe']);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if (env('Production')) {
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
                if (env('Production')) {
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
        $statFilter = $request['statfilter'];
        $mirror = $request['mirror'];

        $cacheKey = 'GlobalHeroTalentStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        /*
if (! env('Production')) {
            Cache::store('database')->forget($cacheKey);
        }
*/
        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
            $hero,
            $gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $mirror,
            $region,
            $statFilter
        ) {

            $data = GlobalHeroTalentDetails::query()
                ->join('heroes', 'heroes.id', '=', 'global_hero_talents_details.hero')
                ->select('name', 'hero as id', 'win_loss', 'talent', 'global_hero_talents_details.level')
                ->selectRaw('SUM(global_hero_talents_details.games_played) as games_played')
                ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                    return $query->selectRaw("SUM(global_hero_talents_details.$statFilter) as total_filter_type");
                })
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByHero($hero)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('hero', 'win_loss', 'talent', 'global_hero_talents_details.level')
                ->orderBy('global_hero_talents_details.level')
                ->orderBy('talent')
                ->orderBy('win_loss')
                ->with(['talentInfo'])
                // ->toSql();
                ->get();

            $data = collect($data)->groupBy('level')->map(function ($levelGroup) {

                $totalGamesPlayed = collect($levelGroup)->sum('games_played');

                return $levelGroup->groupBy('talent')->map(function ($talentGroup) use ($totalGamesPlayed) {
                    $firstItem = $talentGroup->first();

                    $wins = $talentGroup->where('win_loss', 1)->sum('games_played');
                    $losses = $talentGroup->where('win_loss', 0)->sum('games_played');
                    $gamesPlayed = $wins + $losses;
                    $talentInfo = $firstItem->talentInfo;

                    $winRate = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0;
                    $popularity = $totalGamesPlayed > 0 ? round(($gamesPlayed / $totalGamesPlayed) * 100, 2) : 0;

                    $statFilterTotal = $talentGroup->sum('total_filter_type');

                    if (isset($talentInfo['hero_name']) && $talentInfo['hero_name'] == $firstItem['name']) {
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
        $statFilter = $request['statfilter'];
        $mirror = $request['mirror'];
        $talentbuildType = $request['talentbuildtype'];

        $cacheKey = 'GlobalHeroTalentStatsBuilds|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        /*
        if (! env('Production')) {
            Cache::store('database')->forget($cacheKey);
        }
        */

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
            $hero,
            $gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $mirror,
            $region,
            $statFilter,
            $talentbuildType
        ) {
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

            return $topBuilds;
        });

        $talentData = HeroesDataTalent::where('hero_name', $heroModel->name)->get();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $sortBy = $statFilter == 'win_rate' ? 'win_rate' : 'total_filter_type';

        $data->transform(function ($item) use ($talentData, $heroModel) {
            $wins = $item['buildData']['wins'];
            $losses = $item['buildData']['losses'];
            $gamesPlayed = $wins + $losses;
            $winRate = $gamesPlayed > 0 ? $wins / $gamesPlayed : 0;

            // Add win rate to the item
            $item['games_played'] = $gamesPlayed;
            $item['win_rate'] = round($winRate * 100, 2);
            $item['hero'] = $heroModel;
            $item['level_one'] = isset($talentData[$item['level_one']]) ? $talentData[$item['level_one']] : null;
            $item['level_four'] = isset($talentData[$item['level_four']]) ? $talentData[$item['level_four']] : null;
            $item['level_seven'] = isset($talentData[$item['level_seven']]) ? $talentData[$item['level_seven']] : null;
            $item['level_ten'] = isset($talentData[$item['level_ten']]) ? $talentData[$item['level_ten']] : null;
            $item['level_thirteen'] = isset($talentData[$item['level_thirteen']]) ? $talentData[$item['level_thirteen']] : null;
            $item['level_sixteen'] = isset($talentData[$item['level_sixteen']]) ? $talentData[$item['level_sixteen']] : null;
            $item['level_twenty'] = isset($talentData[$item['level_twenty']]) ? $talentData[$item['level_twenty']] : null;

            $item['total_filter_type'] = ($gamesPlayed > 0 ? round($item['buildData']['total_filter_type'] / $gamesPlayed, 2) : 0);

            return $item;
        });
        $data = $data->sortByDesc($sortBy)->values();

        return $data;
    }

    private function topBuildsOnPopularity($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region)
    {
        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(games_played) AS games_played')
            ->filterByGameVersion($gameVersion)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->whereNot('level_twenty', 0)
            ->groupBy('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->orderBy('games_played', 'DESC')
            ->limit($this->buildsToReturn)
            // ->toSql();
            ->get();

        return $data;
    }

    private function topBuildsOnHPAlgorithm($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region)
    {
        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(games_played) AS games_played')
            ->filterByGameVersion($gameVersion)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->whereNot('level_twenty', 0)
            ->groupBy('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->orderBy('games_played', 'DESC')
            ->limit(100)
            // ->toSql();
            ->get();
        $uniqueRows = collect();
        $seenCombinations = [];

        foreach ($data as $row) {
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

        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(games_played) AS games_played')
            ->filterByGameVersion($gameVersion)
            ->filterByGameType($gameType)
            ->filterByHero($hero)
            ->filterByLeagueTier($leagueTier)
            ->filterByHeroLeagueTier($heroLeagueTier)
            ->filterByRoleLeagueTier($roleLeagueTier)
            ->filterByGameMap($gameMap)
            ->filterByHeroLevel($heroLevel)
            ->filterByRegion($region)
            ->whereNot('level_twenty', 0)
            ->groupBy('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->orderBy('games_played', 'DESC')
            ->limit(100)
            // ->toSql();
            ->get();
        $filteredData = $data->unique($columnName)
            ->sortByDesc('games_played')
            ->take($this->buildsToReturn);

        return $filteredData;
    }

    private function getBatchTopBuildsData($builds, $hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $statFilter)
    {
        if ($builds->isEmpty()) {
            return [];
        }

        // Build the query with OR conditions for all builds and their progressive levels
        $query = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(games_played) AS games_played')
            ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                return $query->selectRaw("SUM(global_hero_talents.$statFilter) as total_filter_type");
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
            ->where(function ($outerQuery) use ($builds) {
                foreach ($builds as $build) {
                    // For each build, add conditions for all progressive levels
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
            ->groupBy('win_loss', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->get();

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
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->selectRaw('SUM(games_played) AS games_played')
            ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                return $query->selectRaw("SUM(global_hero_talents.$statFilter) as total_filter_type");
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
}
