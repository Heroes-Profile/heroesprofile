<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\BattlenetAccount;
use App\Models\FriendFoeCache;
use App\Models\GameType;
use App\Rules\DateInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\StackSizeInputValidation;
use App\Services\GlobalQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FriendFoeController extends Controller
{
    private const CACHE_TTL_SECONDS = 1200;

    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

        if (request()->has('game_type')) {
            $validationRules['game_type'] = ['sometimes', 'nullable', new GameTypeInputValidation];
        }
        if (request()->has('season')) {
            $validationRules['season'] = ['sometimes', 'nullable', new SeasonInputValidation];
        }

        if (request()->has('game_map')) {
            $validationRules['game_map'] = ['sometimes', 'nullable', new GameMapInputValidation];
        }

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region'), $validationRules);

        if ($validator->fails()) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        $season = $request['season'];
        $game_type = $request['game_type'];
        $game_map = $request['game_map'];

        return view('Player.friendfoe')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'season' => $season,
            'game_type' => $game_type,
            'game_map' => $game_map,
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
        ]);

    }

    public function getFriendFoeData(Request $request)
    {
        $validationRules = [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation],
            'start_date' => ['sometimes', 'nullable', new DateInputValidation],
            'end_date' => ['sometimes', 'nullable', new DateInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
            'type' => 'required|in:friend,enemy',
            'groupsize' => ['sometimes', 'nullable', new StackSizeInputValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        if (config('app.env') !== 'production') {
            return response()->json($this->executeFriendFoeData($request));
        }

        $paramsHash = hash('sha256', json_encode([
            'blizz_id' => $request['blizz_id'],
            'region' => $request['region'],
            'type' => $request['type'],
            'game_type' => $request['game_type'],
            'season' => $request['season'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'hero' => $request['hero'],
            'game_map' => $request['game_map'],
            'groupsize' => $request['groupsize'],
        ]));

        $dbCache = FriendFoeCache::where('params_hash', $paramsHash)->first();

        if ($dbCache) {
            $latestReplayId = $this->getLatestReplayId(
                $request['blizz_id'],
                $request['region'],
                $request['game_type'],
                $request['season'],
                $request['start_date'],
                $request['end_date']
            );

            if (! $latestReplayId || $dbCache->latest_replayID >= $latestReplayId) {
                return response()->json(json_decode($dbCache->data, true));
            }
        }

        $cacheKey = 'FriendFoeData|'.$paramsHash;

        return app(GlobalQueryService::class)->dispatchAsync(
            $cacheKey,
            static::class,
            'executeFriendFoeData',
            $request->all(),
            self::CACHE_TTL_SECONDS
        );
    }

    public function executeFriendFoeData(Request $request)
    {
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();

        $season = $request['season'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $type = $request['type'];
        $teamValue = $type == 'friend' ? 0 : 1;
        // The page's map filter is a multi-select.
        $gameMap = $request['game_map'] ? $this->globalDataService->getGameMapFilterValues((array) $request['game_map']) : null;
        $hero = $request['hero'];
        $groupSize = $request['groupsize'];

        // A solo player is stored as stack_size 0 or 1.
        $groupSize = match ($groupSize) {
            'Solo' => [0, 1],
            'Duo' => [2],
            '3 Players' => [3],
            '4 Players' => [4],
            '5 Players' => [5],
            default => null,
        };

        $innerQuery = DB::table('replay')
            ->select('replay.replayID', 'player.party')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->tap(function ($query) use ($season, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season, $startDate, $endDate);
            })
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when(! is_null($groupSize), function ($query) use ($groupSize) {
                return $query->whereIn('stack_size', $groupSize);
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
            ->get();

        $teamValue = $type == 'friend' ? 1 : 0;

        $innerQuery = DB::table('replay')
            ->select('replay.replayID', 'player.party')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->tap(function ($query) use ($season, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season, $startDate, $endDate);
            })
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when(! is_null($groupSize), function ($query) use ($groupSize) {
                return $query->whereIn('stack_size', $groupSize);
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

        // Private and banned team-mates and opponents are left out entirely. No viewer
        // exception: this runs in the async worker and the result is shared by everyone.
        $checkedData = $groupedResultsByBlizzId->reject(function ($group) use ($region) {
            return $this->globalDataService->isHiddenFrom($group->first()->blizz_id, $region);
        });

        // Same rule as checkIfSiteFlair: only Patreon accounts with site flair enabled.
        $patreonAccounts = BattlenetAccount::without(['patreonAccount', 'userSettings'])
            ->whereHas('patreonAccount', fn ($query) => $query->where('site_flair', 1))
            ->get(['blizz_id', 'region']);

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
                'hero_games' => $heroData['total_games_played'],
                'region' => $region,
                'hp_owner' => $this->globalDataService->showOwnerFlair($blizz_id, $region),
                'patreon' => ! (is_null($patreonAccount) || empty($patreonAccount) || count($patreonAccount) == 0)
                    && ! $this->globalDataService->isFlairHidden('patreon', $blizz_id, $region),
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

        $latestReplayId = $this->getLatestReplayId(
            $blizz_id,
            $region,
            $request['game_type'],
            $request['season'],
            $request['start_date'],
            $request['end_date']
        );

        $paramsHash = hash('sha256', json_encode([
            'blizz_id' => $blizz_id,
            'region' => $region,
            'type' => $type,
            'game_type' => $request['game_type'],
            'season' => $request['season'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'hero' => $request['hero'],
            'game_map' => $request['game_map'],
            'groupsize' => $request['groupsize'],
        ]));

        FriendFoeCache::updateOrCreate(
            ['params_hash' => $paramsHash],
            [
                'blizz_id' => $blizz_id,
                'region' => $region,
                'type' => $type,
                'latest_replayID' => $latestReplayId ?? 0,
                'data' => json_encode($finalResults),
            ]
        );

        return $finalResults;
    }

    private function getLatestReplayId($blizz_id, $region, $game_type, $season, $startDate = null, $endDate = null): ?int
    {
        $gameTypeIds = $game_type
            ? GameType::whereIn('short_name', (array) $game_type)->pluck('type_id')->toArray()
            : null;

        $query = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->when($gameTypeIds, fn ($q) => $q->whereIn('game_type', $gameTypeIds))
            ->tap(function ($q) use ($season, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($q, $season, $startDate, $endDate);
            });

        return $query->max('replay.replayID');
    }
}
