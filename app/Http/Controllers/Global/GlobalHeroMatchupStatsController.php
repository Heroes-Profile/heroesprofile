<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;
use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\HeroInputValidation;
use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;

class GlobalHeroMatchupStatsController extends Controller
{
    private $buildsToReturn;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);
        return view('Global.Matchups.globalMatchupsStats', compact('userinput'));
    }

    public function getHeroMatchupData(Request $request){
        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);
        $gameVersion = (new TimeframeMinorInputValidation())->passes('timeframe', explode(',', $request["timeframe"]));
        $gameType = (new GameTypeInputValidation())->passes('game_type', explode(',', $request["game_type"]));
        $leagueTier = (new TierInputValidation())->passes('league_tier', explode(',', $request["league_tier"]));
        $heroLeagueTier = (new TierInputValidation())->passes('hero_league_tier', explode(',', $request["hero_league_tier"]));
        $roleLeagueTier = (new TierInputValidation())->passes('role_league_tier', explode(',', $request["role_league_tier"]));
        $gameMap = (new GameMapInputValidation())->passes('map', explode(',', $request["map"]));
        $heroLevel = (new HeroLevelInputValidation())->passes('hero_level', explode(',', $request["hero_level"]));
        $mirror = (new MirrorInputValidation())->passes('mirror', $request["mirror"]);
        $region = (new RegionInputValidation())->passes('region', $request["region"]);

        $cacheKey = "GlobalMatchupStats|" . implode('|', [
            'hero' => $hero,
            'gameVersion' => implode(',', $gameVersion),
            'gameType' => implode(',', $gameType),
            'leagueTier' => implode(',', $leagueTier),
            'heroLeagueTier' => implode(',', $heroLeagueTier),
            'roleLeagueTier' => implode(',', $roleLeagueTier),
            'gameMap' => implode(',', $gameMap),
            'heroLevel' => implode(',', $heroLevel),
            'mirror' => $mirror,
            'region' => implode(',', $region),
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
                ->select('ally', 'win_loss',)
                ->selectRaw('SUM(games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByHero($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('ally')
                ->groupBy('win_loss')
                //->toSql();
                ->get();
            $allyData = $this->combineData($allyData, "ally");
            $enemyData = GlobalHeromatchupsEnemy::query()
                ->select('enemy', 'win_loss',)
                ->selectRaw('SUM(games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByHero($gameType)
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
            return ["ally" => $allyData, "enemy" => $enemyData];
        });
        return $data;
    }

    private function combineData($data, $type){
        $combinedData = collect($data)->groupBy($type)->map(function ($group) use ($type){
            $firstItem = $group->first();
            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            return [
                $type => $firstItem[$type],
                'wins' => $wins,
                'losses' => $losses,
                'gamesPlayed' => $gamesPlayed,
                'win_rate' => $gamesPlayed != 0 ? ($wins / $gamesPlayed) * 100 : 0
            ];

        })->sortByDesc('win_rate')->values()->toArray();

        return $combinedData;
    }
}
