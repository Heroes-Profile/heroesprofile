<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;

use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\GlobalHeroStats;
use App\Models\Hero;

class GlobalHeroStatsController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        $maxReplayID = $this->globalDataService->calculateMaxReplayNumber();
        $latestPatch = $this->globalDataService->getLatestPatch();
        $latestGameDate = $this->globalDataService->getLatestGameDate();

        return view('Global.Hero.globalHeroStats', compact('maxReplayID', 'latestPatch', 'latestGameDate'));
    }

    public function getGlobalHeroData(Request $request){
        $gameVersion = ['2.55.3.90670']; /*$request["gameVersion"];*/
        $gameType = [5]; /*$request["gameType"];*/
        $leagueTier = []; /*$request["leagueTier"];*/
        $heroLeagueTier = []; /*$request["heroLeagueTier"];*/
        $roleLeagueTier = []; /*$request["roleLeagueTier"];*/
        $gameMap = []; /*$request["gameMap"];*/
        $heroLevel = []; /*$request["heroLevel"];*/
        $mirror = 0; /*$request["mirror"];*/


        $cacheKey = "GlobalHeroStats|" . implode('_', [
            'gameVersion' => implode(',', $gameVersion),
            'gameType' => implode(',', $gameType),
            'leagueTier' => implode(',', $leagueTier),
            'heroLeagueTier' => implode(',', $heroLeagueTier),
            'roleLeagueTier' => implode(',', $roleLeagueTier),
            'gameMap' => implode(',', $gameMap),
            'heroLevel' => implode(',', $heroLevel),
            'mirror' => $mirror,
        ]);

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion, 
                                                                                                                                 $gameType, 
                                                                                                                                 $leagueTier, 
                                                                                                                                 $heroLeagueTier,
                                                                                                                                 $roleLeagueTier,
                                                                                                                                 $gameMap,
                                                                                                                                 $heroLevel,
                                                                                                                                 $mirror
                                                                                                                                ){
  
            $data = GlobalHeroStats::query()
                ->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                ->select('heroes.name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'heroes.new_role as role')
                ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByHeroLevel($heroLevel)
                ->filterByGameMap($gameMap)
                ->excludeMirror($mirror)
                ->groupBy('global_hero_stats.hero', 'global_hero_stats.win_loss')
                ->orderBy('heroes.name', 'asc')
                ->orderBy('global_hero_stats.win_loss', 'asc')
                //->toSql();
                ->get();
            return $data;
        });

        return $data;
    }
}
