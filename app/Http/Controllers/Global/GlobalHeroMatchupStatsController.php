<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\HeroInputValidation;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Models\Hero;
use App\Models\GameType;

class GlobalHeroMatchupStatsController extends GlobalsInputValidationController
{
    public function show(Request $request){
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);
        return view('Global.Matchups.globalMatchupsStats')->with([
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
            ]);
    }

    public function getHeroMatchupData(Request $request){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

       $validationRules = array_merge($this->globalsValidationRules($request["timeframe_type"]), [
            'hero' => ['required', new HeroInputValidation()],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                "data" => $request->all(),
                "status" => "failure to validate inputs"
            ];
        }


        $hero = session('heroes')->keyBy('name')[$request["hero"]]->id;
        $gameVersion = $this->getTimeframeFilterValues($request["timeframe_type"], $request["timeframe"]);
        $gameTypeRecords = GameType::whereIn("short_name", $request["game_type"])->get();
        $gameType = $gameTypeRecords->pluck("type_id")->toArray();
        $leagueTier = $request["league_tier"];
        $heroLeagueTier = $request["hero_league_tier"];
        $roleLeagueTier = $request["role_league_tier"];
        $gameMap = $this->getGameMapFilterValues($request["game_map"]);
        $heroLevel = $request["hero_level"];
        $region = $this->getRegionFilterValues($request["region"]);
        $statFilter = $request["statfilter"];
        $mirror = $request["mirror"];

        $cacheKey = "GlobalMatchupStats|" . json_encode($request->all());

        //return $gameMap;

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

        $combinedData = collect($data)->groupBy($type)->map(function ($group) use ($type, $heroData) {
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

        $found = false;
        $notFound = [];

        foreach ($heroData as $hero) {
            $found = false;
            foreach ($combinedData as $data) {
                if ($data["hero"]["id"] == $hero->id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $notFound[] = $hero;
            }
        }

        foreach ($notFound as $hero) {
            // Add the missing hero as a separate array to $combinedData
            $combinedData[] = [
                'hero' => $hero,
                'wins' => 0,
                'losses' => 0,
                'games_played' => 0,
                'win_rate' => 0.0,
                'hovertext' => "",
            ];
        }

        return $combinedData;

    }
}
