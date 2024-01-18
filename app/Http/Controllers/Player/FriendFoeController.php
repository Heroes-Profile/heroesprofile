<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\BattlenetAccount;
use App\Models\GameType;
use App\Models\Map;
use App\Models\SeasonDate;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\StackSizeInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FriendFoeController extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

        if (request()->has('game_type')) {
            $validationRules['game_type'] = ['sometimes', 'nullable', new GameTypeInputValidation()];
        }
        if (request()->has('season')) {
            $validationRules['season'] = ['sometimes', 'nullable', new SeasonInputValidation()];
        }

        if (request()->has('game_map')) {
            $validationRules['game_map'] = ['sometimes', 'nullable', new GameMapInputValidation()];
        }

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('battletag', 'blizz_id', 'region'),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];
        $game_type = $request['game_type'];
        $game_map = $request['game_map'];

        return view('Player.friendfoe')->with([
            'regions' => $this->globalDataService->getRegionIDtoString(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'season' => $season,
            'game_type' => $game_type,
            'game_map' => $game_map,
            'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
        ]);

    }

    public function getFriendFoeData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
            'type' => 'sometimes|in:friend,enemy',
            'groupsize' => ['sometimes', 'nullable', new StackSizeInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();
        $season = $request['season'];
        $type = $request['type'];
        $teamValue = $type == 'friend' ? 0 : 1;
        $gameMap = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id') : null;
        $hero = $request['hero'];
        $groupSize = $request['groupsize'];

        if ($groupSize == 'Solo') {
            $groupSize = 0;
        } elseif ($groupSize == 'Duo') {
            $groupSize = 2;
        } elseif ($groupSize == '3 Players') {
            $groupSize = 3;
        } elseif ($groupSize == '4 Players') {
            $groupSize = 4;
        } elseif ($groupSize == '5 Players') {
            $groupSize = 5;
        }

        $innerQuery = DB::table('replay')
            ->select('replay.replayID', 'player.party')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->when(! is_null($season), function ($query) use ($season) {
                $data = SeasonDate::where('id', $season)->first();
                if ($data) {
                    $query->where('game_date', '>=', $data->start_date)
                        ->where('game_date', '<', $data->end_date);
                }

                return $query;
            })
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when(! is_null($groupSize), function ($query) use ($groupSize) {
                return $query->where('stack_size', $groupSize);
            })
            ->where('team', $teamValue)
            ->get();

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
            ->whereIn('replay.replayID', $innerQuery->pluck('replayID')->toArray())
            ->when(! is_null($groupSize) && $type == 'friend', function ($query) use ($innerQuery) {
                return $query->whereIn('party', $innerQuery->pluck('party')->toArray());
            })
            ->where('team', 0)
            ->groupBy('hero', 'team', 'winner', 'player.blizz_id', 'battletag')
            //->toSql();
            ->get();

        $teamValue = $type == 'friend' ? 1 : 0;

        $innerQuery = DB::table('replay')
            ->select('replay.replayID', 'player.party')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->when(! is_null($season), function ($query) use ($season) {
                $data = SeasonDate::where('id', $season)->first();
                if ($data) {
                    $query->where('game_date', '>=', $data->start_date)
                        ->where('game_date', '<', $data->end_date);
                }

                return $query;
            })
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when(! is_null($groupSize), function ($query) use ($groupSize) {
                return $query->where('stack_size', $groupSize);
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
            ->whereIn('replay.replayID', $innerQuery->pluck('replayID')->toArray())
            ->when(! is_null($groupSize) && $type == 'friend', function ($query) use ($innerQuery) {
                return $query->whereIn('party', $innerQuery->pluck('party')->toArray());
            })
            ->where('team', 1)
            ->groupBy('hero', 'team', 'winner', 'player.blizz_id', 'battletag')
            ->get();

        $combinedResults = $result_team_zero->merge($result_team_one);

        $groupedResultsByBlizzId = $combinedResults->groupBy('blizz_id');

        $heroDataByID = $this->globalDataService->getHeroes();
        $heroDataByID = $heroDataByID->keyBy('id');

        $privateAccounts = $this->globalDataService->getPrivateAccounts();
        $checkedData = $groupedResultsByBlizzId->reject(function ($group) use ($privateAccounts, $region) {
            $blizzId = $group->first()->blizz_id;

            return $privateAccounts->contains(function ($account) use ($blizzId, $region) {
                return $account['blizz_id'] == $blizzId && $account['region'] == $region;
            });
        });

        $patreonAccounts = BattlenetAccount::has('patreonAccount')->get();

        $finalResults = $checkedData->map(function ($data, $blizz_id) use ($heroDataByID, $region, $patreonAccounts) {
            $totalWins = $data->where('winner', 1)->sum('total');
            $totalLosses = $data->where('winner', 0)->sum('total');

            $heroData = $data->groupBy('hero')->map(function ($heroData, $hero) use ($heroDataByID) {
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
            $patreonAccount = $patreonAccounts->where('blizz_id', $blizz_id)->where('region', $region);

            return [
                'blizz_id' => $blizz_id,
                'hero' => $heroData['hero']['name'],
                'region' => $region,
                'hp_owner' => ($blizz_id == 67280 && $region = 1) ? true : false,
                'patreon' => is_null($patreonAccount) || empty($patreonAccount) || count($patreonAccount) == 0 ? false : true,
                'battletag' => explode('#', $data->first()->battletag)[0],
                'total_wins' => $totalWins,
                'total_losses' => $totalLosses,
                'total_games_played' => $gamesPlayed,
                'win_rate' => $gamesPlayed ? round(($totalWins / $gamesPlayed) * 100, 2) : 0,
                'heroData' => $heroData,
            ];
        })
            ->filter(function ($data) use ($blizz_id) {
                return $data['blizz_id'] != $blizz_id;
            })
            ->sortByDesc('total_games_played')
            ->take(50)
            ->values()
            ->toArray();

        return $finalResults;
    }
}
