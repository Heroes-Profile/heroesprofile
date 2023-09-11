<?php

namespace App\Http\Controllers\Player;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GlobalDataService;

use App\Rules\SeasonInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;

use App\Models\Hero;
use App\Models\SeasonDate;

class FriendFoeController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        ///Add in some rule handling for game type and season

        $data = [
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region
        ];

        $validator = \Validator::make($data, [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }

        return view('Player.friendfoe')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'gametypedefault' => [$this->globalDataService->getGameTypeDefault()],
                'filters' => $this->globalDataService->getFilterData()
                ]);

    }

    public function getFriendFoeData(Request $request){
        //return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ]);

        if($request['type'] != "friend" && $request['type'] != "enemy"){
            return;
        }

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $type = $request['type'];
        $teamValue = $type == "friend" ? 0 : 1;

        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        $gameMap = (new GameMapInputValidation())->passes('map', $request["map"]);
        $hero = (new HeroInputByIDValidation())->passes('statfilter', $request["hero"]);
        $season = (new SeasonInputValidation())->passes('season', $request["season"]);

        $innerQuery = DB::table('replay')
            ->select('replay.replayID')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->when(is_int($season), function ($query) use ($season) {
                $data = SeasonDate::where("id", $season)->first();
                if ($data) {
                    $query->where('game_date', '>=', $data->start_date)
                          ->where('game_date', '<=', $data->end_date);
                }
                return $query;
            })
            ->when(!empty($gameMap), function ($query) use ($region, $gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(is_int($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->where('team', $teamValue);


        $result_team_zero = DB::table('replay')
            ->select(
                'hero',
                'team',
                'winner',
                'player.blizz_id',
                DB::raw(
                    '(SELECT battletag 
                      FROM heroesprofile.battletags 
                      WHERE blizz_id = player.blizz_id 
                        AND region = replay.region 
                      ORDER BY latest_game DESC 
                      LIMIT 1) AS battletag'
                ),
                DB::raw('COUNT(*) AS total')
            )
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('battletags', 'battletags.player_id', '=', 'player.battletag')
            ->whereIn('replay.replayID', $innerQuery)
            ->where('team', 0)
            ->groupBy('hero', 'team', 'winner', 'player.blizz_id', "battletag")
            ->get();


        $teamValue = $type == "friend" ? 1 : 0;

        $innerQuery = DB::table('replay')
            ->select('replay.replayID')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->when(is_int($season), function ($query) use ($season) {
                $data = SeasonDate::where("id", $season)->first();
                if ($data) {
                    $query->where('game_date', '>=', $data->start_date)
                          ->where('game_date', '<=', $data->end_date);
                }
                return $query;
            })
            ->when(!empty($gameMap), function ($query) use ($region, $gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(is_int($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->where('team', $teamValue);

        $result_team_one = DB::table('replay')
            ->select(
                'hero',
                'team',
                'winner',
                'player.blizz_id',
                DB::raw(
                    '(SELECT battletag 
                      FROM heroesprofile.battletags 
                      WHERE blizz_id = player.blizz_id 
                        AND region = replay.region 
                      ORDER BY latest_game DESC 
                      LIMIT 1) AS battletag'
                ),
                DB::raw('COUNT(*) AS total')
            )
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('battletags', 'battletags.player_id', '=', 'player.battletag')
            ->whereIn('replay.replayID', $innerQuery)
            ->where('team', 1)
            ->groupBy('hero', 'team', 'winner', 'player.blizz_id', "battletag")
            ->get();


        $combinedResults = $result_team_zero->merge($result_team_one);

        $groupedResultsByBlizzId = $combinedResults->groupBy('blizz_id');

        $heroDataByID = $this->$globalDataService->getHeroes();
        $heroDataByID = $heroDataByID->keyBy('id');


        $finalResults = $groupedResultsByBlizzId->map(function($data, $blizz_id) use($heroDataByID, $region){
            $totalWins = $data->where('winner', 1)->sum('total');
            $totalLosses = $data->where('winner', 0)->sum('total');

            $heroData = $data->groupBy('hero')->map(function($heroData, $hero) use ($heroDataByID){
                $totalWins = $heroData->where('winner', 1)->sum('total');
                $totalLosses = $heroData->where('winner', 0)->sum('total');
                return [
                    'hero' => $heroDataByID[$hero],
                    'total_wins' => $totalWins,
                    'total_losses' => $totalLosses,
                    'total_games_played' => $totalWins + $totalLosses,
                ];
            })->sortByDesc('total_games_played')->first();
            $gamesPlayed = $totalWins + $totalLosses;
            return [
                'blizz_id' => $blizz_id,
                'hero' => $heroData["hero"]["name"],
                'region' => $region,
                'battletag' => explode('#', $data->first()->battletag)[0],
                'total_wins' => $totalWins,
                'total_losses' => $totalLosses,
                'total_games_played' => $gamesPlayed,
                'win_rate' => $gamesPlayed ? round(($totalWins / $gamesPlayed) * 100, 2): 0,
                'heroData' => $heroData,
            ];
        })
        ->filter(function($data) use ($blizz_id) {
            return $data['blizz_id'] != $blizz_id;
        })
        ->sortByDesc('total_games_played')
        ->take(50)
        ->values()
        ->toArray();
        return $finalResults;
    }
}
