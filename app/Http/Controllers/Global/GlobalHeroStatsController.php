<?php

namespace App\Http\Controllers\Global;
use Illuminate\Support\Facades\Cache;

use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\MirrorInputValidation;
use App\Rules\RegionInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;

use App\Models\Hero;
use App\Models\GlobalHeroStats;
use App\Models\GlobalHeroStatsBans;
use App\Models\GlobalHeroChange;
use App\Models\SeasonGameVersion;

class GlobalHeroStatsController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        return view('Global.Hero.globalHeroStats')
        ->with([
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
            'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
            'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            'defaultbuildtype' => $this->globalDataService->getDefaultBuildType()
        ]);
    }

    public function getGlobalHeroData(Request $request){    
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
        $statFilter = (new StatFilterInputValidation())->passes('statfilter', $request["statfilter"]);
        $hero = (new HeroInputByIDValidation())->passes('statfilter', $request["hero"]);
        $role = (new RoleInputValidation())->passes('role', $request["role"]);


        $cacheKey = "GlobalHeroStats|" . implode('|', [
            'gameVersion=' . implode(',', $gameVersion),
            'gameType=' . implode(',', $gameType),
            'leagueTier=' . implode(',', $leagueTier),
            'heroLeagueTier=' . implode(',', $heroLeagueTier),
            'roleLeagueTier=' . implode(',', $roleLeagueTier),
            'gameMap=' . implode(',', $gameMap),
            'heroLevel=' . implode(',', $heroLevel),
            'mirror=' . $mirror,
            'region=' . implode(',', $region),
            'statFilter=' . $statFilter,
            'hero=' . $hero,
            'role=' . $role
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
                                                                                                                                 $statFilter,
                                                                                                                                 $hero,
                                                                                                                                 $role
                                                                                                                                ){
  
            $data = GlobalHeroStats::query()
                ->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                ->select('heroes.name', 'heroes.short_name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'heroes.new_role as role')
                ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                    return $query->selectRaw("SUM(global_hero_stats.$statFilter) as total_filter_type");
                })
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
                    ->filterByGameVersion($this->calculateGameVersionsForHeroChange($gameVersion))
                    ->filterByGameType($gameType)
                    //->toSql();
                    ->get();
            }

            return $this->combineData($data, $statFilter, $banData, $changeData, $hero, $role);
        });
        return $data;
    }

    private function calculateGameVersionsForHeroChange($gameVersion){
        //Fix later
        return [SeasonGameVersion::select("game_version")->where('id', (SeasonGameVersion::select("id")->where('game_version', '2.55.3.90670')->first()->id - 1))->first()->game_version];
    }

    private function combineData($data, $statFilter, $banData, $changeData, $hero, $role){
        $totalGamesPlayed = collect($data)->sum('games_played') / 10;

        $sortBy = $statFilter == "win_rate" ? "win_rate" : "total_filter_type";
        $combinedData = collect($data)->groupBy('name')->map(function ($group) use ($banData, $statFilter, $changeData, $totalGamesPlayed) {
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

            $confidenceInterval = $this->calculateWinRateConfidenceInterval($wins, $gamesPlayed);

            $statFilterTotal = $group->sum('total_filter_type');

            return [
                'name' => $firstItem['name'],
                'short_name' => $firstItem['short_name'],
                'hero_id' => $firstItem['hero_id'],
                'role' => $firstItem['role'],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => round($winRate, 2),
                'ban_rate' => round($banRate, 2),
                'win_rate_change' => round($winRate - $changeWinRate, 2),
                'popularity' => round($popularity, 2),
                'pick_rate' => round($pickRate, 2),
                'influence' => $influence,
                'confidence_interval' => round($confidenceInterval,2),
                'total_filter_type' => $gamesPlayed > 0 ? round($statFilterTotal / $gamesPlayed, 2) : 0
            ];
        })->sortByDesc($sortBy)->values()->toArray();


        if($hero){
            $combinedData = collect($combinedData)->filter(function ($item) use ($hero){
                return $item['hero_id'] == $hero;
            });
        }

        if($role){
            $combinedData = collect($combinedData)->filter(function ($item) use ($role){
                return $item['role'] == $role;
            });
        }

        $combinedCollection = collect($combinedData);

        $positiveInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] > 0;
        });

        $negativeInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] < 0;
        });

        $positiveWinRateChangeCollection = $combinedCollection->filter(function ($item) {
            return $item['win_rate_change'] > 0;
        });

        $negativeWinRateChangeCollection = $combinedCollection->filter(function ($item) {
            return $item['win_rate_change'] < 0;
        });

        $averageWinRate = $combinedCollection->avg('win_rate');
        $averageConfidenceInterval = $combinedCollection->avg('confidence_interval');
        $averagePopularity = $combinedCollection->avg('popularity');
        $averagePickRate = $combinedCollection->avg('pick_rate');
        $averageBanRate = $combinedCollection->avg('ban_rate');
        $averagePositiveInfluence = $positiveInfluenceCollection->avg('influence');
        $averageNegativeInfluence = $negativeInfluenceCollection->avg('influence');
        $averagePositiveWinRateChange = $positiveWinRateChangeCollection->avg('win_rate_change');
        $averageNegativeWinRateChange = $negativeWinRateChangeCollection->avg('win_rate_change');
        $averageGamesPlayed = $combinedCollection->sum('games_played') / count($combinedCollection);
        $averageTotalFilterType = $combinedCollection->avg('total_filter_type');

        return [
            'average_win_rate' => round($averageWinRate, 2), 
            'average_confidence_interval' => round($averageConfidenceInterval, 2), 
            'average_popularity' => round($averagePopularity, 2),  
            'average_pick_rate' => round($averagePickRate, 2),  
            'average_ban_rate' => round($averageBanRate, 2),  
            'average_positive_influence' => round($averagePositiveInfluence, 0),
            'average_negative_influence' => round($averageNegativeInfluence, 0),
            'average_positive_win_rate_change' => round($averagePositiveWinRateChange, 2), 
            'average_negative_win_rate_change' => round($averageNegativeWinRateChange, 2), 
            'average_games_played' => round($averageGamesPlayed, 0), 
            'averaege_total_filter_type' => round($averageTotalFilterType, 0),
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
        if ($totalGamesPlayed == 0) {
            return 0; // Or whatever you'd like to return when no games are played
        }

        $winRate = ($wins / $totalGamesPlayed) * 100;
        $zScore = 1.96; // For a 95% confidence level, you might want to map other confidence levels to their respective z-scores

        $confidence = ($zScore * sqrt(($winRate / 100 * (1 - $winRate / 100)) / $totalGamesPlayed)) * 100;
        
        return $confidence;
    }
}
