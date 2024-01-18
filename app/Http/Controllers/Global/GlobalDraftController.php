<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroDraftOrder;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalDraftController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null)
    {
        if (! is_null($hero)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation()],
            ];

            $validator = Validator::make(['hero' => $hero], $validationRules);

            if ($validator->fails()) {
                return back();
            }
        }
        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Global.Draft.globalDraftStats')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'regions' => $this->globalDataService->getRegionIDtoString(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            ]);
    }

    public function getDraftData(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type']), [
            'hero' => ['required', new HeroInputValidation()],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero = $this->getHeroFilterValue($request['hero']);
        $gameVersion = $this->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameType = $this->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->getRegionFilterValues($request['region']);

        $cacheKey = 'GlobalDraftStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        //return $cacheKey;

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
            $hero,
            $gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $region,
        ) {

            $data = GlobalHeroDraftOrder::query()
                ->select('pick_number', 'win_loss')
                ->selectRaw('SUM(count) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->filterByRegion($region)
                ->filterByHero($hero)
                ->groupBy('pick_number', 'win_loss')
                ->orderBy('pick_number')
                ->orderBy('win_loss')
                //->toSql();
                //return $data;
                ->get();

            $totalGamesPlayed = $data->sum('games_played');

            $data = $data->groupBy('pick_number')->map(function ($group) use ($totalGamesPlayed) {
                $firstItem = $group->first();

                $wins = round($group->where('win_loss', 1)->sum('games_played'));
                $losses = round($group->where('win_loss', 0)->sum('games_played'));
                $gamesPlayed = $wins + $losses;

                $winRate = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0;

                $popularity = $totalGamesPlayed > 0 ? round(($gamesPlayed / $totalGamesPlayed) * 100, 2) : 0;

                return [
                    'pick_number' => $firstItem['pick_number'],
                    'wins' => $wins,
                    'losses' => $losses,
                    'games_played' => $gamesPlayed,
                    'popularity' => $popularity,
                    'win_rate' => $winRate,
                ];
            })->values()->toArray();

            return $data;
        });

        return $data;
    }
}
