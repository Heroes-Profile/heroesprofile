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
use App\Rules\RoleInputValidation;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Models\Hero;

class GlobalHeroMatchupStatsController extends Controller
{
    private $buildsToReturn;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);
        return view('Global.Matchups.globalMatchupsStats')->with([
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => [$this->globalDataService->getGameTypeDefault()],
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
            ]);
    }

    public function getHeroMatchupData(Request $request){
        //return response()->json($request->all());

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
        $mirror = (new MirrorInputValidation())->passes('mirror', $request["mirror"]);
        $region = (new RegionInputValidation())->passes('region', $request["region"]);
        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);
        $role = (new RoleInputValidation())->passes('role', $request["role"]);

        $cacheKey = "GlobalMatchupStats|" . implode('|', [
            'hero=' . $hero,
            'gameVersion=' . implode(',', $gameVersion),
            'gameType=' . implode(',', $gameType),
            'leagueTier=' . implode(',', $leagueTier),
            'heroLeagueTier=' . implode(',', $heroLeagueTier),
            'roleLeagueTier=' . implode(',', $roleLeagueTier),
            'gameMap=' . implode(',', $gameMap),
            'heroLevel=' . implode(',', $heroLevel),
            'mirror=' . $mirror,
            'region=' . implode(',', $region),
            'role=' . $role
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
                                                                                                                         $mirror,
                                                                                                                         $region
                                                                                                                        ){
  
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
                ->orderBy("ally")
                //->toSql();
                ->get();
            $allyData = $this->combineData($allyData, "ally");

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
                //->toSql();
                ->get();
            $enemyData = $this->combineData($enemyData, "enemy");


            $allyDataKeyed = collect($allyData)->keyBy(function($item) {
                return $item['hero']['name'];
            });

            $enemyDataKeyed = collect($enemyData)->keyBy(function($item) {
                return $item['hero']['name'];
            });

            // Combine the collections
            $combinedData = $allyDataKeyed->map(function($allyItem, $heroName) use ($enemyDataKeyed) {
                $enemyItem = $enemyDataKeyed->get($heroName);
                if ($enemyItem) {
                    return [
                        'ally' => $allyItem,
                        'enemy' => $enemyItem
                    ];
                }
                return [
                    'ally' => $allyItem,
                    'enemy' => null
                ];
            })->sortKeys()->values()->toArray();


            return ["ally" => $allyData, "enemy" => $enemyData, "combined" => $combinedData];
        });
        return $data;
    }

    private function combineData($data, $type){

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $combinedData = collect($data)->groupBy($type)->map(function ($group) use ($type, $heroData){
            $firstItem = $group->first();
            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $winRate = $gamesPlayed != 0 ? ($wins / $gamesPlayed) * 100 : 0;
            $winRate = $type == "ally" ? $winRate : 100 - $winRate;



            return [
                'hero' => $heroData[$firstItem[$type]],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => round($winRate, 2),
                'hovertext' => $type == "ally" ? "Won while on a team with " . $heroData[$firstItem[$type]]["name"] .  " " . round($winRate, 2) . "%" . " of the time." : "Lost against a team with " . $heroData[$firstItem[$type]]["name"] .  " " . round($winRate, 2) . "%" . " of games."
            ];

        })->sortByDesc('win_rate')->values()->toArray();

        return $combinedData;
    }
}
