<?php

namespace App\Http\Controllers\Global;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;
use App\Rules\PartyCombinationRule;

use App\Models\GlobalHeroStackSize;

class GlobalPartyStatsController extends Controller
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
        $hero = (new HeroInputByIDValidation())->passes('hero', $request["hero"]);

        $request->validate([
            'heropartysize' => 'nullable|integer',
        ]);
        $heropartysize = $request["heropartysize"];

        $teamoneparty = (new PartyCombinationRule())->passes('teamoneparty', $request["teamoneparty"]);
        $teamtwoparty = (new PartyCombinationRule())->passes('teamtwoparty', $request["teamtwoparty"]);



        $cacheKey = "GlobalPartyStats|" . implode('|', [
            'gameVersion=' . implode(',', $gameVersion),
            'gameType=' . implode(',', $gameType),
            'leagueTier=' . implode(',', $leagueTier),
            'heroLeagueTier=' . implode(',', $heroLeagueTier),
            'roleLeagueTier=' . implode(',', $roleLeagueTier),
            'gameMap=' . implode(',', $gameMap),
            'heroLevel=' . implode(',', $heroLevel),
            'mirror=' . $mirror,
            'region=' . implode(',', $region),
            'hero=' . $hero,
            'heropartysize=' . $heropartysize,
            'teamoneparty=' . $teamoneparty,
            'teamtwoparty=' . $teamtwoparty,

        ]);

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
