<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;

use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Hero;
use App\Models\GlobalHeroStats;
use App\Models\GlobalHeroStatsBans;
use App\Models\GlobalHeroChange;


use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;


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
        //http://127.0.0.1:8000/api/v1/global/hero/?timeframe=2.55.3.90670,2.54.2.85894&game_type=qm,sl&league_tier=wood,gold,blah&hero_league_tier=diamond&role_league_tier=master&map=Alterac%20Pass&hero_level=5&mirror=Include

        $gameVersion = (new TimeframeMinorInputValidation())->passes('timeframe', explode(',', $request["timeframe"]));
        $gameType = (new GameTypeInputValidation())->passes('game_type', explode(',', $request["game_type"]));
        $leagueTier = (new TierInputValidation())->passes('league_tier', explode(',', $request["league_tier"]));
        $heroLeagueTier = (new TierInputValidation())->passes('hero_league_tier', explode(',', $request["hero_league_tier"]));
        $roleLeagueTier = (new TierInputValidation())->passes('role_league_tier', explode(',', $request["role_league_tier"]));
        $gameMap = (new GameMapInputValidation())->passes('map', explode(',', $request["map"]));
        $heroLevel = (new HeroLevelInputValidation())->passes('hero_level', explode(',', $request["hero_level"]));
        $mirror = (new MirrorInputValidation())->passes('mirror', $request["mirror"]);
        $region = (new RegionInputValidation())->passes('region', $request["region"]);

        $cacheKey = "GlobalHeroStats|" . implode('|', [
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

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion, 
                                                                                                                                 $gameType, 
                                                                                                                                 $leagueTier, 
                                                                                                                                 $heroLeagueTier,
                                                                                                                                 $roleLeagueTier,
                                                                                                                                 $gameMap,
                                                                                                                                 $heroLevel,
                                                                                                                                 $mirror,
                                                                                                                                 $region
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
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->excludeMirror($mirror)
                ->filterByRegion($region)
                ->groupBy('global_hero_stats.hero', 'global_hero_stats.win_loss')
                ->orderBy('heroes.name', 'asc')
                ->orderBy('global_hero_stats.win_loss', 'asc')
                //->toSql();
                ->get();

            $banData = GlobalHeroStatsBans::query()
                ->join('heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                ->select('heroes.name', 'heroes.id as hero_id')
                ->selectRaw('SUM(global_hero_stats_bans.bans) as bans')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->filterByRegion($region)
                ->groupBy('global_hero_stats_bans.hero')
                ->orderBy('heroes.name', 'asc')
                //->toSql();
                ->get();

            $changeData = null;

            if($this->checkIfChange($gameVersion, $region, $gameType, $gameMap, $leagueTier, $heroLeagueTier, $roleLeagueTier, $heroLevel)){
                $changeData = GlobalHeroChange::query()
                    ->join('heroesprofile.heroes', 'heroesprofile.heroes.id', '=', 'global_hero_change.hero')
                    ->select('heroes.name', 'heroes.id as hero_id', 'win_rate as change_win_rate')
                    ->filterByGameVersion($gameVersion)
                    ->filterByGameType($gameType)
                    //->toSql();
                    ->get();
            }

            return $this->combineData($data, $banData, $changeData);
        });



        return $data;
    }
    private function combineData($data, $banData, $changeData){
        $totalGamesPlayed = collect($data)->sum('games_played') / 10;

        $combinedData = collect($data)->groupBy('name')->map(function ($group) use ($banData, $changeData, $totalGamesPlayed) {
            $firstItem = $group->first();

            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $winRate = 0;
            if($gamesPlayed > 0){
                $winRate = ($wins / $gamesPlayed) * 100;
            }

            $matchingBan = $banData->where('name', $firstItem['name'])->first();
            $bans = $matchingBan ? round($matchingBan['bans']) : 0; // Round the bans value

            $banRate = 0;
            if($bans > 0){
                $banRate = ($bans / $totalGamesPlayed) * 100;
            }

            $changeWinRate = 0;

            if ($changeData) {
                $matchingChange = $changeData->where('name', $firstItem['name'])->first();
                if ($matchingChange) {
                    $changeWinRate = $matchingChange['change_win_rate'];
                }
            }

            $popularity = (($gamesPlayed + $bans) / $totalGamesPlayed) * 100;
            $pickRate = ($gamesPlayed / $totalGamesPlayed) * 100;

            $adjustedPickRate = (($gamesPlayed / $totalGamesPlayed) * 100) / (100 - $banRate);
            $influence = round((($winRate / 100) - 0.5) * ($adjustedPickRate * 10000));

            $confidenceInterval = $this->calculateWinRateConfidenceInterval($wins, $totalGamesPlayed);

            return [
                'name' => $firstItem['name'],
                'hero_id' => $firstItem['hero_id'],
                'role' => $firstItem['role'],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => $winRate,
                'ban_rate' => $banRate,
                'win_rate_change' => $winRate - $changeWinRate,
                'popularity' => $popularity,
                'pick_rate' => $pickRate,
                'influence' => $influence,
                'confidence_interval' => $confidenceInterval,
            ];
        })->sortByDesc('win_rate')->values()->toArray();

        $combinedCollection = collect($combinedData);

        $positiveInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] > 0;
        });

        $negativeInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] < 0;
        });

        $positiveWinRateChangeCollection = $combinedCollection->filter(function ($item) {
            return $item['change_win_rate'] > 0;
        });

        $negativeWinRateChangeCollection = $combinedCollection->filter(function ($item) {
            return $item['change_win_rate'] < 0;
        });

        $averageWinRate = $combinedCollection->avg('win_rate');
        $averageConfidenceInterval = $combinedCollection->avg('confidence_interval');
        $averageWinRateChange = $combinedCollection->avg('change_win_rate');
        $averagePopularity = $combinedCollection->avg('popularity');
        $averagePickRate = $combinedCollection->avg('pick_rate');
        $averageBanRate = $combinedCollection->avg('ban_rate');
        $averagePositiveInfluence = $positiveInfluenceCollection->avg('influence');
        $averageNegativeInfluence = $negativeInfluenceCollection->avg('influence');
        $averagePositiveWinRateChange = $positiveWinRateChangeCollection->avg('change_win_rate');
        $averageNegativeWinRateChange = $negativeWinRateChangeCollection->avg('change_win_rate');
        $averageGamesPlayed = $combinedCollection->sum('games_played') / count($combinedCollection);

        return [
            'average_win_rate' => $averageWinRate, 
            'average_confidence_interval' => $averageConfidenceInterval, 
            'average_win_rate_change' => $averageWinRateChange, 
            'average_popularity' => $averagePopularity, 
            'average_pick_rate' => $averagePickRate, 
            'average_ban_rate' => $averageBanRate, 
            'average_positive_influence' => $averagePositiveInfluence,
            'average_negative_influence' => $averageNegativeInfluence,
            'average_positive_win_rate_change' => $averagePositiveWinRateChange,
            'average_negative_win_rate_change' => $averageNegativeWinRateChange,
            'average_games_played' => $averageGamesPlayed, 
            'data' => $combinedData
        ];
    }

    //🤮
    private function checkIfChange($timeframe, $region, $game_type, $map, $league_tier, $hero_league_tier, $role_league_tier, $hero_level){
        if(
            count($timeframe) == 1 &&
            count($game_type) == 1 &&
            empty($region) &&
            empty($map) &&
            empty($league_tier) &&
            empty($hero_league_tier) &&
            empty($role_league_tier) &&
            empty($hero_level)
        ){
            return true;
        }
        return false;
    }

    private function calculateWinRateConfidenceInterval($wins, $totalGamesPlayed, $confidenceLevel = 0.95) {
        $z = 1.96; // For a 95% confidence level
        $p = $wins / $totalGamesPlayed;
        $n = $totalGamesPlayed;

        $a = 1 / (1 + (1 / $n) * $z * $z);
        $b = $p + (1 / (2 * $n)) * $z * $z;
        $c = $z * sqrt(($p * (1 - $p) / $n) + ($z * $z / (4 * $n * $n)));

        $lowerBound = $a * ($b - $c);
        $upperBound = $a * ($b + $c);

        //Using average of the two for now but may decide to change it back later
        return ($lowerBound + $upperBound) / 2;
    }
}
