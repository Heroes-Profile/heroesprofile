<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;
use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\HeroInputValidation;
use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;

use App\Models\GlobalHeroStats;
use App\Models\GlobalHeroStatsBans;
use App\Models\Map;

class GlobalHeroMapStatsController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);
        return view('Global.Hero.Map.globalHeroMapStats')
                     ->with([
                        'userinput' => $userinput,
                        'filters' => $this->globalDataService->getFilterData(),
                        'gametypedefault' => [$this->globalDataService->getGameTypeDefault()],
                        'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                        'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                    ]);
    }

    public function getHeroStatMapData(Request $request){
        //return response()->json($request->all());
        $hero = (new HeroInputValidation())->passes('userinput', $request["userinput"]);
        $gameVersion = null;
        if($request["timeframe_type"] == "major"){
            $gameVersions = SeasonGameVersion::select('game_version')
                                            ->where('game_version', 'like', $request["timeframe"][0] . "%")
                                            ->pluck('game_version')
                                            ->toArray();                                            
            $gameVersion = (new TimeframeMinorInputValidation())->passes('timeframe', $gameVersions);

        }else{
            $gameVersion = (new TimeframeMinorInputValidation())->passes('timeframe', $request["timeframe"]);
        }
        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        $leagueTier = (new TierByIDInputValidation())->passes('league_tier', $request["league_tier"]);
        $heroLeagueTier = (new TierByIDInputValidation())->passes('hero_league_tier', $request["hero_league_tier"]);
        $roleLeagueTier = (new TierByIDInputValidation())->passes('role_league_tier', $request["role_league_tier"]);
        $heroLevel = (new HeroLevelInputValidation())->passes('hero_level', $request["hero_level"]);
        $mirror = (new MirrorInputValidation())->passes('mirror', $request["mirror"]);
        $region = (new RegionInputValidation())->passes('region', $request["region"]);

        $cacheKey = "GlobalHeroMapStats|" . implode('|', [
            'hero' => $hero,
            'gameVersion=' . implode(',', $gameVersion),
            'gameType=' . implode(',', $gameType),
            'leagueTier=' . implode(',', $leagueTier),
            'heroLeagueTier=' . implode(',', $heroLeagueTier),
            'roleLeagueTier=' . implode(',', $roleLeagueTier),
            'heroLevel=' . implode(',', $heroLevel),
            'mirror=' . $mirror,
            'region=' . implode(',', $region),
        ]);

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
                                                                                                                        ){
  
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
                ->groupBy("win_loss")
                ->groupBy("hero")
                ->groupBy("map_id")
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
                ->groupBy("hero")
                ->groupBy("map_id")
                //->toSql();
                ->get();


            return $this->combineData($hero, $data, $banData);
        });
        return $data;

    }

    private function combineData($hero, $data, $banData){
        $gamesPlayedPerMap = $data->groupBy('map_id')->map(function($items) {
            return $items->sum('games_played') / 10;
        })->toArray();

        $filteredData = $data->filter(function ($item) use ($hero){
            return $item->hero_id === $hero;
        });

        $mapData = Map::all();
        $mapData = $mapData->keyBy('map_id');

        $totalGamesPlayed = ($filteredData)->sum('games_played');

        return collect($filteredData)->groupBy(function($date) {
            return $date['name'].$date['map_id'];
        })->map(function ($group) use ($banData, $gamesPlayedPerMap, $mapData, $totalGamesPlayed) {
            $firstItem = $group->first();

            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $matchingBan = $banData->first(function ($value) use ($firstItem) {
                return $value['name'] === $firstItem['name'] && $value['map_id'] === $firstItem['map_id'];
            });
            $bans = $matchingBan ? round($matchingBan['bans']) : 0;

            $totalGamesForThisMap = $gamesPlayedPerMap[$firstItem['map_id']] ?? 0;

            return [
                'name' => $mapData[$firstItem['map_id']]["name"],
                'map' => $mapData[$firstItem['map_id']],
                'win_rate' => $gamesPlayed != 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'games_played' => $gamesPlayed,
                'ban_rate' => $totalGamesForThisMap != 0 ? round(($bans / $totalGamesForThisMap) * 100, 2) : 0,
                'total_games_played' => $totalGamesPlayed,
            ];
        })->sortByDesc('win_rate')->values()->toArray();
    }
}
