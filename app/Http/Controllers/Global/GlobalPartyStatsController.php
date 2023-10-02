<?php

namespace App\Http\Controllers\Global;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use App\Rules\PartyCombinationRule;
use App\Rules\HeroInputByIDValidation;


use App\Models\GlobalHeroStackSize;

class GlobalPartyStatsController extends GlobalsInputValidationController
{
    public function show(Request $request){
        return view('Global.Party.globalPartyStats')
        ->with([
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
            'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
            'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            'defaultbuildtype' => $this->globalDataService->getDefaultBuildType()
        ]);
    }


    public function getPartyStats(Request $request){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());


        $validationRules = array_merge($this->globalsValidationRules($request["timeframe_type"]), [
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
            'teamoneparty' => ['sometimes', 'nullable', new PartyCombinationRule()],
            'teamtwoparty' => ['sometimes', 'nullable', new PartyCombinationRule()],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                "data" => $request->all(),
                "status" => "failure to validate inputs"
            ];
        }


        $hero = $request["hero"];
        $gameVersion = $this->getTimeframeFilterValues($request["timeframe_type"], $request["timeframe"]);
        $gameType = $this->getGameTypeFilterValues($request["game_type"]); 
        $leagueTier = $request["league_tier"];
        $heroLeagueTier = $request["hero_league_tier"];
        $roleLeagueTier = $request["role_league_tier"];
        $gameMap = $this->getGameMapFilterValues($request["game_map"]);
        $heroLevel = $request["hero_level"];
        $region = $this->getRegionFilterValues($request["region"]);
        $mirror = $request["mirror"];
        $heropartysize = $request["heropartysize"];

        $teamoneparty = $request["teamoneparty"];
        $teamtwoparty = $request["teamtwoparty"];



        $cacheKey = "GlobalPartyStats|"  . json_encode($request->all());

        //return $cacheKey;

        $data = Cache::store("database")->remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion, 
                                                                                                                                 $gameType, 
                                                                                                                                 $leagueTier, 
                                                                                                                                 $heroLeagueTier,
                                                                                                                                 $roleLeagueTier,
                                                                                                                                 $gameMap,
                                                                                                                                 $heroLevel,
                                                                                                                                 $mirror,
                                                                                                                                 $region,
                                                                                                                                 $hero,
                                                                                                                                 $heropartysize,
                                                                                                                                 $teamoneparty,
                                                                                                                                 $teamtwoparty
                                                                                                                                ){
  
            $data = GlobalHeroStackSize::query()
                ->select('team_ally_stack_value', 'team_enemy_stack_value')
                ->selectRaw('SUM(games_played) as games_played')
                ->selectRaw('SUM(IF(win_loss = 1, games_played, 0)) AS wins')
                ->selectRaw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->filterByHero($hero)
                ->filterByAllyStackSize($teamoneparty)
                ->filterByEnemyStackSize($teamtwoparty)
                ->groupBy('team_ally_stack_value', 'team_enemy_stack_value')
                ->orderBy('team_ally_stack_value', 'asc')
                //->toSql();
                ->get();

            $returnData = [];
            $total = 0;

            $divideValue = 1;

            if(!$hero){
              $divideValue = 10;
            }

            $party_combinations = [
                '00005' => '5 Solo',
                '00023' => '1 Double, 3 Solo',
                '00041' => '2 Double, 1 Solo',
                '00302' => '1 Triple, 2 Solo',
                '00320' => '1 Triple, 1 Double',
                '04001' => '1 Quad, 1 Solo',
                '50000' => '1 team of 5'
            ];

            foreach ($data as $row) {
                $total += $row->wins + $row->losses;

                $comboType = '';
                switch ($row->team_ally_stack_value) {
                    case '00005':
                        $comboType = 'solo';
                        break;
                    case '00023':
                        $comboType = 'double';
                        break;
                    case '00041':
                        $comboType = 'double_double';
                        break;
                    case '00302':
                        $comboType = 'triple';
                        break;
                    case '00320':
                        $comboType = 'triple_double';
                        break;
                    case '04001':
                        $comboType = 'quadruple';
                        break;
                    case '50000':
                        $comboType = 'quintuple';
                        break;
                }

                if ($comboType) {
                    $combo = $row->team_ally_stack_value . '|' . $row->team_enemy_stack_value;

                    $returnData[$comboType][$combo]['ally_combo'] = $row->team_ally_stack_value;
                    $returnData[$comboType][$combo]['enemy_combo'] = $row->team_enemy_stack_value;
                    $returnData[$comboType][$combo]['wins'] = round($row->wins / $divideValue);
                    $returnData[$comboType][$combo]['losses'] = round($row->losses / $divideValue);

                    $gamesPlayed = $returnData[$comboType][$combo]['wins'] + $returnData[$comboType][$combo]['losses'];

                    $returnData[$comboType][$combo]['win_rate'] = $gamesPlayed ? round(($returnData[$comboType][$combo]['wins'] / $gamesPlayed) * 100, 2): 0;

                    $returnData[$comboType][$combo]['stack_size_name'] = $party_combinations[$row->team_enemy_stack_value];
                }
            }

            return $returnData;

        });
        return $data;
    }
}
