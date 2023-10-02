<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Rules\HeroInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\TalentBuildTypeInputValidation;

use App\Models\GlobalHeroTalentDetails;
use App\Models\GlobalHeroTalents;
use App\Models\TalentCombination;
use App\Models\HeroesDataTalent;
use App\Models\SeasonGameVersion;
use App\Models\GameType;

class GlobalTalentStatsController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null){
          if (!is_null($hero)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation()],
            ];

            $validator = Validator::make(['hero' => $hero], $validationRules);

            if ($validator->fails()) {
                return back();
            }
        }


    
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);

        return view('Global.Talents.globalTalentStats')
            ->with([
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            ]);
    }

    public function getGlobalHeroTalentData(Request $request){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request["timeframe_type"]), [
            'statfilter' => ['required', new StatFilterInputValidation()],
            'hero' => ['required', new HeroInputValidation()],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                "data" => $request->all(),
                "status" => "failure to validate inputs"
            ];
        }


        $hero = $this->getHeroFilterValue($request["hero"]);
        $gameVersion = $this->getTimeframeFilterValues($request["timeframe_type"], $request["timeframe"]);
        $gameType = $this->getGameTypeFilterValues($request["game_type"]); 
        $leagueTier = $request["league_tier"];
        $heroLeagueTier = $request["hero_league_tier"];
        $roleLeagueTier = $request["role_league_tier"];
        $gameMap = $this->getGameMapFilterValues($request["game_map"]);
        $heroLevel = $request["hero_level"];
        $region = $this->getRegionFilterValues($request["region"]);
        $statFilter = $request["statfilter"];
        $mirror = $request["mirror"];

        $cacheKey = "GlobalHeroTalentStats|" . json_encode($request->all());

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
                                                                                                                         $region,
                                                                                                                         $statFilter
                                                                                                                        ){
  
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
                //->toSql();
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

                    return [
                        'name' => $firstItem['name'],
                        'hero_id' => $firstItem['id'],
                        'wins' => $wins,
                        'losses' => $losses,
                        'games_played' => $gamesPlayed,
                        'popularity' => $popularity,
                        'win_rate' => $winRate,
                        'level' => $firstItem['level'],
                        'sort' => $talentInfo["sort"],
                        'talentInfo' => $talentInfo,
                        'total_filter_type' => $gamesPlayed > 0 ? round($statFilterTotal / $gamesPlayed, 2) : 0
                    ];
                })->sortBy("sort")->values()->toArray();
            });


            return $data;
        });
        return $data;
    }

    public function getGlobalHeroTalentBuildData(Request $request){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request["timeframe_type"]), [
            'statfilter' => ['required', new StatFilterInputValidation()],
            'hero' => ['required', new HeroInputValidation()],
            'talentbuildtype' => ['required', new TalentBuildTypeInputValidation()],
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
        $talentbuildType = $request["talentbuildtype"];

        $cacheKey = "GlobalHeroTalentStats|" . json_encode($request->all());
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
                                                                                                                 $region,
                                                                                                                 $statFilter,
                                                                                                                 $talentbuildType
                                                                                                                ){
            $topBuilds = null;
            if($talentbuildType == "Popular"){
                $topBuilds = $this->topBuildsOnPopularity($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region);
            }else if($talentbuildType == "HP Algorithm"){
                $topBuilds = $this->topBuildsOnHPAlgorithm($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region);
            } else if (strpos($talentbuildType, 'Unique') !== false) {
                preg_match('/\d+/', $talentbuildType, $matches);
                $uniqueLevel = $matches[0];
                $topBuilds = $this->topBuildsOnUniqueLevel($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $uniqueLevel);
            }

            foreach ($topBuilds as $build) {
                $build->buildData = $this->getTopBuildsData($build, 1, $hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $statFilter);
            }


   

            return $topBuilds;
        });

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $sortBy = $statFilter == "win_rate" ? "win_rate" : "total_filter_type";

        $data->transform(function ($item) use ($talentData, $heroData) {
            $wins = $item['buildData']['wins'];
            $losses = $item['buildData']['losses'];
            $gamesPlayed = $wins + $losses;
            $winRate = $gamesPlayed > 0 ? $wins / $gamesPlayed : 0;


            // Add win rate to the item
            $item['games_played'] = $gamesPlayed;
            $item['win_rate'] = round($winRate * 100, 2);
            $item['hero'] = $heroData[$item['hero']];
            $item['level_one'] = $talentData[$item['level_one']];
            $item['level_four'] = $talentData[$item['level_four']];
            $item['level_seven'] = $talentData[$item['level_seven']];
            $item['level_ten'] = $talentData[$item['level_ten']];
            $item['level_thirteen'] = $talentData[$item['level_thirteen']];
            $item['level_sixteen'] = $talentData[$item['level_sixteen']];
            $item['level_twenty'] = $talentData[$item['level_twenty']];
            $item['total_filter_type'] = ($gamesPlayed > 0 ? round($item['buildData']['total_filter_type'] / $gamesPlayed, 2) : 0);

            return $item;
        });
        $data = $data->sortByDesc($sortBy)->values();
        return $data;
    }

    private function topBuildsOnPopularity($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region){
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
            ->whereNot("level_twenty", 0)
            ->groupBy('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->orderBy('games_played', 'DESC')
            ->limit($this->buildsToReturn)
            //->toSql();
            ->get();
        return $data;
    }

    private function topBuildsOnHPAlgorithm($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region){
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
            ->whereNot("level_twenty", 0)
            ->groupBy('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->orderBy('games_played', 'DESC')
            ->limit(100)
            //->toSql();
            ->get();
        $uniqueRows = collect();
        $seenCombinations = [];

        foreach ($data as $row) {
            $combination = $row->level_one . '-' . $row->level_four . '-' . $row->level_seven;

            if (!isset($seenCombinations[$combination])) {
                $uniqueRows->push($row);
                $seenCombinations[$combination] = true;
            }
        }
        $sortedAndLimitedRows = $uniqueRows->sortByDesc('games_played')->take($this->buildsToReturn);
        return $sortedAndLimitedRows;
    }

    private function topBuildsOnUniqueLevel($hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $uniqueLevel){
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
            ->whereNot("level_twenty", 0)
            ->groupBy('heroesprofile.global_hero_talents.hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->orderBy('games_played', 'DESC')
            ->limit(100)
            //->toSql();
            ->get();
        $filteredData = $data->unique($columnName)
                              ->sortByDesc('games_played')
                              ->take($this->buildsToReturn);
        return $filteredData;
    }

    private function getTopBuildsData($build, $win_loss, $hero, $gameVersion, $gameType, $leagueTier, $heroLeagueTier, $roleLeagueTier, $gameMap, $heroLevel, $mirror, $region, $statFilter){
        $transformedData = [
            'wins' => 0,
            'losses' => 0,
            'total_filter_type' => 0,
        ];

        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss')
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
            ->where("level_one", $build->level_one)
            ->where("level_four", $build->level_four)
            ->where("level_seven", $build->level_seven)
            ->where("level_ten", $build->level_ten)
            ->where("level_thirteen", 0)
            ->where("level_sixteen", 0)
            ->where("level_twenty", 0)
            //->toSql();
            ->groupBy("win_loss")
            ->get();

        $transformedData = [
            'wins' => ($transformedData["wins"] + $data->where('win_loss', 1)->sum('games_played')),
            'losses' => ($transformedData["losses"] + $data->where('win_loss', 0)->sum('games_played')),
            'total_filter_type' => $statFilter !== 'win_rate' ? ($transformedData["total_filter_type"] + $data->sum('total_filter_type')) : 0,
        ];


        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss')
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
            ->where("level_one", $build->level_one)
            ->where("level_four", $build->level_four)
            ->where("level_seven", $build->level_seven)
            ->where("level_ten", $build->level_ten)
            ->where("level_thirteen", $build->level_thirteen)
            ->where("level_sixteen", 0)
            ->where("level_twenty", 0)
            //->toSql();
            ->groupBy("win_loss")
            ->get();

        $transformedData = [
            'wins' => ($transformedData["wins"] + ($data->where('win_loss', 1)->sum('games_played') * 1.125)),
            'losses' => ($transformedData["losses"] + ($data->where('win_loss', 0)->sum('games_played')* 1.125)),
            'total_filter_type' => $statFilter !== 'win_rate' ? ($transformedData["total_filter_type"] + $data->sum('total_filter_type')) : 0,
        ];
        



        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss')
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
            ->where("level_one", $build->level_one)
            ->where("level_four", $build->level_four)
            ->where("level_seven", $build->level_seven)
            ->where("level_ten", $build->level_ten)
            ->where("level_thirteen", $build->level_thirteen)
            ->where("level_sixteen", $build->level_sixteen)
            ->where("level_twenty", 0)
            //->toSql();
            ->groupBy("win_loss")
            ->get();

        $transformedData = [
            'wins' => ($transformedData["wins"] + ($data->where('win_loss', 1)->sum('games_played') * 1.33)),
            'losses' => ($transformedData["losses"] + ($data->where('win_loss', 0)->sum('games_played')* 1.33)),
            'total_filter_type' => $statFilter !== 'win_rate' ? ($transformedData["total_filter_type"] + $data->sum('total_filter_type')) : 0,
        ];




        $data = GlobalHeroTalents::query()
            ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
            ->select('win_loss')
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
            ->where("level_one", $build->level_one)
            ->where("level_four", $build->level_four)
            ->where("level_seven", $build->level_seven)
            ->where("level_ten", $build->level_ten)
            ->where("level_thirteen", $build->level_thirteen)
            ->where("level_sixteen", $build->level_sixteen)
            ->where("level_twenty", $build->level_twenty)
            //->toSql();
            ->groupBy("win_loss")
            ->get();

        $transformedData = [
            'wins' => round($transformedData["wins"] + ($data->where('win_loss', 1)->sum('games_played') * 1.5)),
            'losses' => round($transformedData["losses"] + ($data->where('win_loss', 0)->sum('games_played')* 1.5)),
            'total_filter_type' => $statFilter !== 'win_rate' ? ($transformedData["total_filter_type"] + $data->sum('total_filter_type')) : 0,
        ];

         return $transformedData;
    }
}
