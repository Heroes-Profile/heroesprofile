<?php

namespace App\Http\Controllers\Global;

use App\Models\GameType;
use App\Models\GlobalCompositions;
use App\Models\MMRTypeID;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalCompositionsController extends GlobalsInputValidationController
{
    public function show(Request $request)
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

        return view('Global.Compositions.compositionsStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'urlparameters' => $request->all(),
                'heroes' => $this->globalDataService->getHeroes(),

            ]);

    }

    public function getCompositionsData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'minimum_games' => 'required|integer',
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
        $talentbuildType = $request['talentbuildtype'];
        $minimumGames = $request['minimum_games'];

        $cacheKey = 'GlobalCompositionStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        /*
        if (! env('Production')) {
            Cache::store('database')->forget($cacheKey);
        }
        */

        $data = Cache::store('database')->remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $hero,
            $mirror,
            $region,
            $minimumGames
        ) {

            $data = GlobalCompositions::query()
                ->select('composition_id', 'win_loss')
                ->selectRaw('SUM(games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->filterByHero($hero)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('composition_id', 'win_loss')
                ->with(['composition'])
                // ->toSql();
                ->get();

            $roleData = MMRTypeID::all();
            $roleData = $roleData->keyBy('mmr_type_id');

            $totalGamesPlayed = $hero ? (collect($data)->sum('games_played')) : (collect($data)->sum('games_played') / 5);

            $filteredData = collect($data)
                ->groupBy('composition_id')
                ->map(function ($group) use ($totalGamesPlayed, $roleData, $minimumGames, $hero) {
                    $wins = $hero ? $group->where('win_loss', 1)->sum('games_played') : $group->where('win_loss', 1)->sum('games_played') / 5;
                    $losses = $hero ? $group->where('win_loss', 0)->sum('games_played') : $group->where('win_loss', 0)->sum('games_played') / 5;
                    $gamesPlayed = ($wins + $losses);

                    if ($gamesPlayed <= $minimumGames) {
                        return null;
                    }
                    $winRate = 0;
                    if ($gamesPlayed > 0) {
                        $winRate = (($wins) / $gamesPlayed) * 100;
                    }

                    $popularity = round(($gamesPlayed / $totalGamesPlayed) * 100, 2);

                    $compositionData = $group->first()['composition'];

                    $role_one = $roleData[$group->first()['composition']['role_one']];
                    $role_two = $roleData[$group->first()['composition']['role_two']];
                    $role_three = $roleData[$group->first()['composition']['role_three']];
                    $role_four = $roleData[$group->first()['composition']['role_four']];
                    $role_five = $roleData[$group->first()['composition']['role_five']];

                    return [
                        'composition_id' => $group->first()['composition_id'],
                        'wins' => $wins,
                        'losses' => $losses,
                        'win_rate' => round($winRate, 2),
                        'games_played' => round($gamesPlayed),
                        'popularity' => $popularity,
                        'role_one' => $role_one,
                        'role_two' => $role_two,
                        'role_three' => $role_three,
                        'role_four' => $role_four,
                        'role_five' => $role_five,
                    ];
                })
                ->filter()
                ->sortByDesc('win_rate')
                ->values()
                ->toArray();

            return $filteredData;

        });

        return $data;
    }

    public function getTopHeroData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'minimum_games' => 'required|integer',
            'composition_id' => 'required|integer',
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
        $minimumGames = $request['minimum_games'];

        $compositionID = $request['composition_id'];

        $cacheKey = 'GlobalCompositionTopHeroes|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        /*
        if (! env('Production')) {
            Cache::store('database')->forget($cacheKey);
        }
        */

        $data = Cache::store('database')->remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $mirror,
            $region,
            $compositionID

        ) {

            $data = GlobalCompositions::query()
                ->select('hero')
                ->selectRaw('SUM(games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                // ->filterByHero($hero)
                ->filterByCompositionID($compositionID)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('hero')
                // ->toSql();
                ->get();

            $heroData = $this->globalDataService->getHeroes();
            $heroData = $heroData->keyBy('id');

            $data = $data->map(function ($item) use ($heroData) {
                $item['role'] = $heroData[$item->hero]['new_role'];
                $item['name'] = $heroData[$item->hero]['name'];
                $item['herodata'] = $heroData[$item->hero];

                return $item;
            });

            // Group the data by role
            $groupedData = $data->groupBy('role');

            // Filter and sort each group, then exclude empty groups
            $result = $groupedData->map(function ($group, $role) {
                return $group->sortByDesc('games_played')->values();
            })->reject(function ($group) {
                return $group->isEmpty();
            })->toArray();

            return $result;

        });

        return $data;
    }
}
