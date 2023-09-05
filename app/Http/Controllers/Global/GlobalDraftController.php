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
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;

use App\Models\GlobalHeroDraftOrder;
use App\Models\GlobalHeroStatsBans;

class GlobalDraftController extends Controller
{
    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);

        return view('Global.Draft.globalDraftStats')
            ->with([
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => [$this->globalDataService->getGameTypeDefault()],
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            ]);
    }

    public function getDraftData(Request $request){
        //return response()->json($request->all());

        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);

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
        $gameMap = (new GameMapInputValidation())->passes('map', $request["map"]);
        $heroLevel = (new HeroLevelInputValidation())->passes('hero_level', $request["hero_level"]);
        $region = (new RegionInputValidation())->passes('region', $request["region"]);

        $cacheKey = "GlobalDraftStats|" . implode('|', [
            'hero' => $hero,
            'gameVersion=' . implode(',', $gameVersion),
            'gameType=' . implode(',', $gameType),
            'leagueTier=' . implode(',', $leagueTier),
            'heroLeagueTier=' . implode(',', $heroLeagueTier),
            'roleLeagueTier=' . implode(',', $roleLeagueTier),
            'gameMap=' . implode(',', $gameMap),
            'heroLevel=' . implode(',', $heroLevel),
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
                                                                                                                         $gameMap,
                                                                                                                         $heroLevel,
                                                                                                                         $region,
                                                                                                                        ){
  
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
