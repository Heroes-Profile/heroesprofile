<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Rules\HeroInputValidation;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Models\GlobalHeroTalentsVersusHeroes;
use App\Models\GlobalHeroTalentsWithHeroes;
use App\Models\Hero;
use App\Models\SeasonGameVersion;
use App\Models\GameType;

class GlobalHeroMatchupsTalentsController extends GlobalsInputValidationController
{
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
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'inputhero' => $inputhero,
                'inputenemyally' => $inputenemyally,
            ]);
    }

    public function getHeroMatchupsTalentsData(Request $request){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request["timeframe_type"]), [
            'hero' => ['required', new HeroInputValidation()],
            'ally_enemy' => ['required', new HeroInputValidation()],
            'type' => 'required|in:Enemy,Ally',
            'talent_view' => 'required|in:hero,ally_enemy',
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                "data" => $request->all(),
                "status" => "failure to validate inputs"
            ];
        }
        $hero = $this->getHeroFilterValue($request["hero"]);
        $allyEnemy = session('heroes')->keyBy('name')[$request["ally_enemy"]]->id;
        $gameTypeRecords = GameType::whereIn("short_name", $request["game_type"])->get();
        $gameType = $gameTypeRecords->pluck("type_id")->toArray();
        $leagueTier = $request["league_tier"];
        $gameMap = $this->getGameMapFilterValues($request["game_map"]);
        $type = $request["type"];
        $talentView = $request["talent_view"];


        $gameVersion = $this->getTimeframeFilterValues($request["timeframe_type"], $request["timeframe"]);
        $gameVersionIDs = SeasonGameVersion::whereIn("game_version", $gameVersion)->pluck("id")->toArray();





        $cacheKey = "GlobalHeroMatchupsTalents|" . json_encode($request->all());

        //return $cacheKey;

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
                                                                                                                         $hero,
                                                                                                                         $allyEnemy,
                                                                                                                         $type,
                                                                                                                         $talentView,
                                                                                                                         $gameVersion, 
                                                                                                                         $gameVersionIDs,
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
                ->filterByGameVersion($gameVersionIDs)
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
            ->filterByHero($hero)
            ->filterByAllyEnemy($allyEnemy)
            ->filterByLeagueTier($leagueTier)
            ->excludeMirror(0)
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
