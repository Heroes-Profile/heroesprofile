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
use App\Rules\TierInputValidation;
use App\Rules\GameMapInputValidation;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Models\GlobalHeroTalentsVersusHeroes;
use App\Models\GlobalHeroTalentsWithHeroes;

class GlobalHeroMatchupsTalentsController extends Controller
{
    private $buildsToReturn;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        return view('Global.Matchups.Talents.globalMatchupsTalentsStats');
    }

    public function getHeroMatchupsTalentsData(Request $request){
        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);
        $allyEnemy = (new HeroInputValidation())->passes('ally_enemy', $request["ally_enemy"]);
        $type = in_array($request["type"], ["Enemy", "Ally"]) ? $request["type"] : "Enemy";
        $talentView = in_array($request["talent_view"], ["hero", "ally_enemy"]) ? $request["talent_view"] : "Enemy";
        $gameVersion = (new TimeframeMinorInputValidation())->passes('timeframe', explode(',', $request["timeframe"]));
        $gameVersionID = (new TimeframeMinorInputValidationOutputID())->passes('timeframe', explode(',', $request["timeframe"]));
        $gameType = (new GameTypeInputValidation())->passes('game_type', explode(',', $request["game_type"]));
        $gameMap = (new GameMapInputValidation())->passes('map', explode(',', $request["map"]));
        $leagueTier = (new TierInputValidation())->passes('league_tier', explode(',', $request["league_tier"]));

        $cacheKey = "GlobalHeroMatchupsTalents|" . implode('|', [
            'hero' => $hero,
            'allyEnemy' => $allyEnemy,
            'type' => $type,
            'talentView' => $talentView,
            'gameVersion' => implode(',', $gameVersion),
            'gameType' => implode(',', $gameType),
            'leagueTier' => implode(',', $leagueTier),
            'gameMap' => implode(',', $gameMap)
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
            $secondHeroWinRate = $firstHeroWinRateData > 50 ? $firstHeroWinRateData - 100 : 100 - $firstHeroWinRateData;


            $model = $type === "Ally" ? GlobalHeroTalentsWithHeroes::class : GlobalHeroTalentsVersusHeroes::class;
            $table = $type === "Ally" ? "global_hero_talents_with_heroes" : "global_hero_talents_versus_heroes";

            $data = $model::query()
                ->join('heroes_data_talents', 'heroes_data_talents.talent_id', '=', $table . '.talent')
                ->select('win_loss', 'icon', 'description', 'heroes_data_talents.level', 'heroes_data_talents.title', 'heroes_data_talents.sort')
                ->selectRaw('SUM(games_played) as games_played')
                ->filterByGameVersion($gameVersionID)
                ->filterByGameType($gameType)
                ->filterByHero($gameType)
                ->filterByAllyEnemy($allyEnemy)
                ->filterByLeagueTier($leagueTier)
                ->groupBy('win_loss' , 'icon' , 'hotkey' , 'description' , 'heroes_data_talents.level' , 'heroes_data_talents.title' , 'heroes_data_talents.sort')
                ->orderBy('heroes_data_talents.level')
                ->orderBy('heroes_data_talents.sort')
                ->orderBy('heroes_data_talents.title')
                ->orderBy('win_loss')
                ->get();

            $data = collect($data)->groupBy('title')->map(function ($group) {
                $firstItem = $group->first();

                $wins = $group->where('win_loss', 1)->sum('games_played');
                $losses = $group->where('win_loss', 0)->sum('games_played');
                $gamesPlayed = $wins + $losses;

                $winRate = 0;
                if($gamesPlayed > 0){
                    $winRate = ($wins / $gamesPlayed) * 100;
                }

                return [
                    'title' => $firstItem['title'],
                    'level' => $firstItem['level'],
                    'sort' => $firstItem['sort'],
                    'description' => $firstItem['description'],
                    'icon' => $firstItem['icon'],
                    'wins' => $wins,
                    'losses' => $losses,
                    'gamesPlayed' => $gamesPlayed,
                ];
            })->values()->toArray();

            return $data;

            return ["win_rate" => $secondHeroWinRate, "data" => $data];
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
