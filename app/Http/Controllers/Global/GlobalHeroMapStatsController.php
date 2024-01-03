<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroStats;
use App\Models\GlobalHeroStatsBans;
use App\Models\Map;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalHeroMapStatsController extends GlobalsInputValidationController
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

        return view('Global.Hero.Map.globalHeroMapStats')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'regions' => $this->globalDataService->getRegionIDtoString(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault("multi"),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            ]);
    }

    public function getHeroStatMapData(Request $request)
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
        $mirror = $request['mirror'];

        $cacheKey = 'GlobalHeroMapStats|' . implode(",", \App\Models\SeasonGameVersion::select("id")->whereIn("game_version", $gameVersion)->pluck("id")->toArray()) . '|' .hash('sha256', json_encode($request->all()));

        //return $cacheKey;

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
            $hero,
            $gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $heroLevel,
            $mirror,
            $region
        ) {

            $data = GlobalHeroStats::query()
                ->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                ->join('maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                ->select('heroes.name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'map_id')
                ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByHeroLevel($heroLevel)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->filterByHero($hero)
                ->groupBy('win_loss')
                ->groupBy('hero')
                ->groupBy('map_id')
                //->toSql();
                ->get();

            $gamesPlayedPerMap = GlobalHeroStats::query()
                ->select('game_map')
                ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByHeroLevel($heroLevel)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('game_map')
                //->toSql();
                ->get();

            $banData = GlobalHeroStatsBans::query()
                ->join('heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                ->join('maps', 'maps.map_id', '=', 'global_hero_stats_bans.game_map')
                ->select('heroes.name', 'heroes.id as hero_id', 'map_id')
                ->selectRaw('SUM(global_hero_stats_bans.bans) as bans')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByHeroLevel($heroLevel)
                ->filterByHero($hero)
                ->filterByRegion($region)
                ->groupBy('hero')
                ->groupBy('map_id')
                //->toSql();
                ->get();

            return $this->combineData($gameType, $hero, $data, $gamesPlayedPerMap, $banData);
        });

        return $data;

    }

    private function combineData($gameType, $hero, $data, $gamesPlayedPerMap, $banData)
    {

        $filteredData = $data->filter(function ($item) use ($hero) {
            return $item->hero_id === $hero;
        });

        $mapData = Map::all();
        $mapData = $mapData->keyBy('map_id');

        $totalGamesPlayed = $filteredData->sum('games_played');

        return collect($filteredData)
            ->groupBy(function ($data) {
                return $data['name'].$data['map_id'];
            })
        //For some reason every hero has 1 game played in ARAM that isnt an ARAM map...something odd in my backend code
            ->filter(function ($group) use ($gameType, $mapData) {
                if (count($gameType) == 1 && $gameType[0] == '6') {
                    $firstItem = $group->first();

                    return $mapData[$firstItem['map_id']]['type'] == 'ARAM';
                }

                // Keep all other maps
                return true;
            })
            ->map(function ($group) use ($banData, $gamesPlayedPerMap, $mapData, $totalGamesPlayed) {
                $firstItem = $group->first();

                $wins = $group->where('win_loss', 1)->sum('games_played');
                $losses = $group->where('win_loss', 0)->sum('games_played');
                $gamesPlayed = $wins + $losses;

                $matchingBan = $banData->first(function ($value) use ($firstItem) {
                    return $value['name'] === $firstItem['name'] && $value['map_id'] === $firstItem['map_id'];
                });
                $bans = $matchingBan ? round($matchingBan['bans']) : 0;

                $gameMapData = $gamesPlayedPerMap->where('game_map', 1)->first();
                $totalGamesForThisMap = $gameMapData ? $gameMapData->games_played / 10 : 0;

                return [
                    'name' => $mapData[$firstItem['map_id']]['name'],
                    'map' => $mapData[$firstItem['map_id']],
                    'win_rate' => $gamesPlayed != 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                    'games_played' => $gamesPlayed,
                    'ban_rate' => $totalGamesForThisMap != 0 ? round(($bans / $totalGamesForThisMap) * 100, 2) : 0,
                    'total_games_played' => $totalGamesPlayed,
                ];
            })
            ->sortByDesc('win_rate')
            ->values()
            ->toArray();
    }
}
