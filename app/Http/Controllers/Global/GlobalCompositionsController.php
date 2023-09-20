<?php

namespace App\Http\Controllers\Global;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;
use App\Rules\HeroInputByIDValidation;

use App\Models\GlobalCompositions;
use App\Models\Composition;
use App\Models\MMRTypeID;
use App\Models\Hero;

class GlobalCompositionsController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        return view('Global.Compositions.compositionsStats')  
            ->with([
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType()
            ]);

   
    }

    public function getCompositionsData(Request $request){
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
        $hero = (new HeroInputByIDValidation())->passes('statfilter', $request["hero"]);

        $request->validate([
            'minimum_games' => 'required|integer',
        ]);
        $minimumGames = $request["minimum_games"];

  



        $cacheKey = "GlobalCompositionStats|" . implode('|', [
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
            'minimumGames=' . $minimumGames,
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
                                                                                                                                 $minimumGames
                                                                                                                                ){
  
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
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('composition_id', 'win_loss')
                ->with(['composition'])
                //->toSql();
                ->get();    

            $roleData = MMRTypeID::all();
            $roleData = $roleData->keyBy('mmr_type_id');

            $totalGamesPlayed = collect($data)->sum('games_played');


            $filteredData = collect($data)
                ->groupBy('composition_id')
                ->map(function ($group) use ($totalGamesPlayed, $minimumGames, $roleData){
                    $wins = $group->where('win_loss', 1)->sum('games_played');
                    $losses = $group->where('win_loss', 0)->sum('games_played');
                    $gamesPlayed = $wins + $losses;

                    $winRate = 0;
                    if ($gamesPlayed > 0) {
                        $winRate = ($wins / $gamesPlayed) * 100;
                    }

                    $popularity = round(($gamesPlayed / $totalGamesPlayed) * 100, 2);

                    $compositionData = $group->first()['composition'];

                    $role_one = $roleData[$group->first()['composition']["role_one"]];;
                    $role_two = $roleData[$group->first()['composition']["role_two"]];;
                    $role_three = $roleData[$group->first()['composition']["role_three"]];;
                    $role_four = $roleData[$group->first()['composition']["role_four"]];;
                    $role_five = $roleData[$group->first()['composition']["role_five"]];;

                    return [
                        'composition_id' => $group->first()['composition_id'],
                        'wins' => $wins,
                        'losses' => $losses,
                        'win_rate' => round($winRate, 2),
                        'games_played' => $gamesPlayed,
                        'popularity' => $popularity,
                        'role_one' => $role_one,
                        'role_two' => $role_two,
                        'role_three' => $role_three, 
                        'role_four' => $role_four,
                        'role_five' => $role_five
                    ];
                })
                ->filter(function ($item) use ($minimumGames){
                    return $item['games_played'] >= $minimumGames;
                })
                ->sortByDesc('win_rate')
                ->values()
                ->toArray();


            return $filteredData;
            
        });
        return $data;
    }

    public function getTopHeroData(Request $request){
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
        $hero = (new HeroInputByIDValidation())->passes('statfilter', $request["hero"]);

        $request->validate([
            'minimum_games' => 'required|integer',
            'composition_id' => 'required|integer',
        ]);
        $minimumGames = $request["minimum_games"];
        $compositionID = $request["composition_id"];

  



        $cacheKey = "GlobalCompositionTopHeroes|" . implode('|', [
            'composition_id=' . $compositionID,
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
            'minimumGames=' . $minimumGames,
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
                                                                                                                                 $minimumGames,
                                                                                                                                 $compositionID
                                                                                                                                ){
  
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
                ->filterByCompositionID($compositionID)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('hero')
                //->toSql();
                ->get();    

            $heroData = $this->globalDataService->getHeroes();
            $heroData = $heroData->keyBy('id');

            $data = $data->map(function ($item) use($heroData) {
                // You can access the existing fields like $item->games_played, $item->hero, etc.
                // Perform your calculations and add new fields.
                
                $item['role'] = $heroData[$item->hero]["new_role"];
                $item['name'] = $heroData[$item->hero]["name"];
                $item['herodata'] = $heroData[$item->hero];
                

                return $item;
            });

            return [
                "Bruiser" => $data->filter(function ($item) {return $item['role'] === 'Bruiser';})->sortByDesc('games_played')->values(),
                "Healer" => $data->filter(function ($item) {return $item['role'] === 'Healer';})->sortByDesc('games_played')->values(),
                "Melee Assassin" => $data->filter(function ($item) {return $item['role'] === 'Melee Assassin';})->sortByDesc('games_played')->values(),
                "Ranged Assassin" => $data->filter(function ($item) {return $item['role'] === 'Ranged Assassin';})->sortByDesc('games_played')->values(),
                "Support" => $data->filter(function ($item) {return $item['role'] === 'Support';})->sortByDesc('games_played')->values(),
                "Tank" => $data->filter(function ($item) {return $item['role'] === 'Tank';})->sortByDesc('games_played')->values(),
            ];
            
        });
        return $data;
    }
}
