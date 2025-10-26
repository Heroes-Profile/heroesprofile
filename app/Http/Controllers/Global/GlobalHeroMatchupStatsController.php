<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalHeroMatchupStatsController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null, $allyenemy = null)
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

        if (! is_null($hero) && ! is_null($allyenemy)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation],
                'allyenemy' => ['required', new HeroInputValidation],
            ];

            $validator = Validator::make(['hero' => $hero, 'allyenemy' => $allyenemy], $validationRules);

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

        return view('Global.Matchups.globalMatchupsStats')->with([
            'heroes' => $this->globalDataService->getHeroes(),
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'userinput' => $userinput,
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
            'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
            'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
            'urlparameters' => $request->all(),
        ]);
    }

    public function getHeroMatchupData(Request $request)
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
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $statFilter = $request['statfilter'];
        $mirror = $request['mirror'];

        $role = $request['role'];

        $cacheKey = 'GlobalMatchupStats|'.hash('sha256', json_encode($gameVersion).'|'.json_encode($request->all()));

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
            $role
        ) {
            $heroData = $this->globalDataService->getHeroes()->keyBy('id');

            $allyData = GlobalHeromatchupsAlly::query()
                ->select('ally', 'win_loss')
                ->selectRaw('SUM(games_played) as games_played')
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
                ->groupBy('ally')
                ->groupBy('win_loss')
                ->orderBy('ally')
              // ->toSql();
                ->get();
            $allyData = $this->combineData($allyData, 'ally', $hero, $role, $heroData);

            $enemyData = GlobalHeromatchupsEnemy::query()
                ->select('enemy', 'win_loss')
                ->selectRaw('SUM(games_played) as games_played')
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
                ->groupBy('enemy')
                ->groupBy('win_loss')
                // ->toSql();
                ->get();
            $enemyData = $this->combineData($enemyData, 'enemy', $hero, $role, $heroData);

            $allyDataKeyed = collect($allyData)->keyBy(function ($item) {
                return $item['hero']['name'];
            });

            $enemyDataKeyed = collect($enemyData)->keyBy(function ($item) {
                return $item['hero']['name'];
            });

            // Combine the collections
            $combinedData = $allyDataKeyed->map(function ($allyItem, $heroName) use ($enemyDataKeyed) {
                $enemyItem = $enemyDataKeyed->get($heroName);
                if ($enemyItem) {
                    return [
                        'ally' => $allyItem,
                        'enemy' => $enemyItem,
                    ];
                }

                return [
                    'ally' => $allyItem,
                    'enemy' => null,
                ];
            })->sortKeys()->values()->toArray();

            return ['ally' => $allyData, 'enemy' => $enemyData, 'combined' => $combinedData];
        });

        $heroData = $this->globalDataService->getAllHeroesGlobalWinRates($request);
        $heroDataByName = collect($heroData)->keyBy('name');

        foreach ($data['combined'] as &$combinedEntry) {
            if (isset($heroDataByName[$combinedEntry['ally']['hero']['name']])) {
                $combinedEntry['ally']['stats'] = $heroDataByName[$combinedEntry['ally']['hero']['name']];
            }

            if (isset($heroDataByName[$combinedEntry['enemy']['hero']['name']])) {
                $combinedEntry['enemy']['stats'] = $heroDataByName[$combinedEntry['enemy']['hero']['name']];
            }
        }

        return $data;
    }

    private function combineData($data, $type, $heroID, $role, $heroData)
    {

        $combinedData = collect($data)->groupBy($type)->map(function ($group) use ($type, $heroData, $role) {
            $firstItem = $group->first();
            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $winRate = $gamesPlayed != 0 ? ($wins / $gamesPlayed) * 100 : 0;
            $winRate = $type == 'ally' ? $winRate : 100 - $winRate;

            if ($role && ($heroData[$firstItem[$type]]['new_role'] != $role)) {
                return null;
            }

            return [
                'hero' => $heroData[$firstItem[$type]],
                'role' => $heroData[$firstItem[$type]]['new_role'],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => round($winRate, 2),
                'hovertext' => $type == 'ally' ? 'Won while on a team with '.$heroData[$firstItem[$type]]['name'].' '.round($winRate, 2).'%'.' of the time.' : 'Lost against a team with '.$heroData[$firstItem[$type]]['name'].' '.round($winRate, 2).'%'.' of games.',
            ];
        })->filter()->sortByDesc('win_rate')->values()->toArray();

        $notFound = [];
        $existingHeroIds = collect($combinedData)->pluck('hero.id')->flip();

        foreach ($heroData as $hero) {
            if ($role && ($hero['new_role'] != $role)) {
                continue;
            }

            if (!isset($existingHeroIds[$hero->id]) && $heroID != $hero->id) {
                $notFound[] = $hero;
            }
        }

        foreach ($notFound as $hero) {
            $combinedData[] = [
                'hero' => $hero,
                'wins' => 0,
                'losses' => 0,
                'games_played' => 0,
                'win_rate' => 0.0,
                'hovertext' => '',
            ];
        }

        return $combinedData;

    }
}
