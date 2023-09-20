<?php

namespace App\Http\Controllers\Global;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\HeroInputValidation;
use App\Rules\TimeframeMinorInputValidation;
use App\Rules\TimeframeMinorInputValidationOutputID;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\GameMapInputValidation;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Models\GlobalHeroTalentsVersusHeroes;
use App\Models\GlobalHeroTalentsWithHeroes;
use App\Models\Hero;

class GlobalHeroMatchupsTalentsController extends Controller
{
    private $buildsToReturn;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        $inputhero = (new HeroInputValidation())->passes('hero', $request["hero"]);
        $inputenemyally = (new HeroInputValidation())->passes('allyenemy', $request["allyenemy"]);

        if(!$inputhero){
            $inputhero = new Hero;
            $inputhero->name = "Auto Select";
            $inputhero->short_name = "autoselect3";
            $inputhero->icon = "autoselect3.jpg";
        }

        if(!$inputenemyally){
            $inputenemyally = new Hero;
            $inputenemyally->name = "Auto Select";
            $inputenemyally->short_name = "autoselect3";
            $inputenemyally->icon = "autoselect3.jpg";
        }

        return view('Global.Matchups.Talents.globalMatchupsTalentsStats')->with([
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'inputhero' => $inputhero,
                'inputenemyally' => $inputenemyally,
            ]);
    }

    public function getHeroMatchupsTalentsData(Request $request){
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
        $gameMap = (new GameMapInputValidation())->passes('map', $request["map"]);
        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);
        $allyEnemy = (new HeroInputValidation())->passes('ally_enemy', $request["ally_enemy"]);
        $type = in_array($request["type"], ["Enemy", "Ally"]) ? $request["type"] : "Enemy";
        $talentView = in_array($request["talent_view"], ["hero", "ally_enemy"]) ? $request["talent_view"] : "Enemy";
        $gameVersionID = (new TimeframeMinorInputValidationOutputID())->passes('timeframe', $gameVersion);

        $cacheKey = "GlobalHeroMatchupsTalents|" . implode('|', [
            'hero=' . $hero,
            'allyEnemy=' . $allyEnemy,
            'type=' . $type,
            'talentView=' . $talentView,
            'gameVersion=' . implode(',', $gameVersion),
            'gameType=' . implode(',', $gameType),
            'leagueTier=' . implode(',', $leagueTier),
            'gameMap=' . implode(',', $gameMap),
        ]);


        //return $cacheKey;

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
                                                                                                                         $hero,
                                                                                                                         $allyEnemy,
                                                                                                                         $type,
                                                                                                                         $talentView,
                                                                                                                         $gameVersion, 
                                                                                                                         $gameVersionID,
                                                                                                                         $gameType, 
                                                                                                                         $leagueTier, 
                                                                                                                         $gameMap,
                                                                                                                        ){
            
            $firstHeroWinRateData = $this->calculateWinRateData($hero, $allyEnemy, $type, $gameVersion, $gameType, $leagueTier, $gameMap);
            $secondHeroWinRate = $type == "Ally" ? round($firstHeroWinRateData , 2) : round(100 - $firstHeroWinRateData, 2);
            $firstHeroWinRateData  = round($firstHeroWinRateData , 2);

            $model = $type === "Ally" ? GlobalHeroTalentsWithHeroes::class : GlobalHeroTalentsVersusHeroes::class;
            $table = $type === "Ally" ? "global_hero_talents_with_heroes" : "global_hero_talents_versus_heroes";

            $data = $model::query()
                ->select('win_loss', 'level', 'talent')
                ->selectRaw('SUM(games_played) as games_played')
                ->filterByGameVersion($gameVersionID)
                ->filterByGameType($gameType)
                ->filterByHero($hero)
                ->filterByAllyEnemy($allyEnemy)
                ->filterByLeagueTier($leagueTier)
                ->groupBy('win_loss' , 'level', 'talent')
                ->orderBy('level')
                ->orderBy('win_loss')
                ->with(['talentInfo'])
                //->toSql();
                //return $data;
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

                    return [
                        'wins' => $wins,
                        'losses' => $losses,
                        'games_played' => $gamesPlayed,
                        'popularity' => $popularity,
                        'win_rate' => $winRate,
                        'level' => $firstItem['level'],
                        'talent' => $firstItem['talent'],
                        'sort' => $talentInfo["sort"],
                        'talentInfo' => $talentInfo,
                    ];
                })->sortBy("sort")->values()->toArray();
            });

            return ["first_win_rate" => $firstHeroWinRateData, "second_win_rate" => $secondHeroWinRate, "data" => $data];
        });
        return $data;
    }

    private function calculateWinRateData($hero, $allyEnemy, $type, $gameVersion, $gameType, $leagueTier, $gameMap){
        $model = $type === "Ally" ? GlobalHeromatchupsAlly::class : GlobalHeromatchupsEnemy::class;

        $data = $model::query()
            ->select('win_loss',)
            ->selectRaw('SUM(games_played) as games_played')
            ->filterByGameVersion($gameVersion)
            ->filterByGameType($gameType)
            ->filterByHero($gameType)
            ->filterByAllyEnemy($allyEnemy)
            ->filterByLeagueTier($leagueTier)
            ->groupBy('win_loss')
            ->get();

        $wins = 0;
        $losses = 0;

        foreach ($data as $record) {
            if ($record->win_loss == 1) {
                $wins = $record->games_played;
            } else if ($record->win_loss == 0) {
                $losses = $record->games_played;
            }
        }

        if ($wins + $losses > 0) {
            $winRate = ($wins / ($wins + $losses)) * 100;
        } else {
            $winRate = 0; 
        }

        return $winRate;
    }
}
