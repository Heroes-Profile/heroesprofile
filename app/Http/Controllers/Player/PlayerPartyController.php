<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\PlayerStatsCache;
use App\Rules\DateInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\SeasonInputValidation;
use App\Services\GlobalQueryService;
use App\Support\GlobalCacheKey;
use App\Support\PartyCombinations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerPartyController extends Controller
{
    private const CACHE_TTL_SECONDS = 1200;

    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

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

        return view('Player.party')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'urlparameters' => $request->query(),
        ]);
    }

    public function getPartyData(Request $request)
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
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        if (! $this->globalDataService->usesProductionCaching()) {
            return response()->json($this->executePartyData($request));
        }

        $paramsHash = $this->paramsHash($request);

        $dbCache = PlayerStatsCache::where('params_hash', $paramsHash)->first();

        if ($dbCache) {
            $latestReplayId = $this->getLatestReplayId($request);

            if (! $latestReplayId || $dbCache->latest_replayID >= $latestReplayId) {
                return response()->json(json_decode($dbCache->data, true));
            }
        }

        if (! $this->globalDataService->isGlobalAsyncEnabled()) {
            return response()->json($this->executePartyData($request));
        }

        return app(GlobalQueryService::class)->dispatchAsync(
            'PlayerPartyData|'.$paramsHash,
            static::class,
            'executePartyData',
            $request->all(),
            self::CACHE_TTL_SECONDS
        );
    }

    public function executePartyData(Request $request)
    {
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();
        $season = $request['season'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $gameMap = $request['game_map'] ? $this->globalDataService->getGameMapFilterValues((array) $request['game_map']) : null;
        $hero = $request['hero'];

        // One row per player in each of the player's games.
        $rows = DB::table('replay')
            ->select('replay.replayID', 'self.team AS self_team', 'self.winner', 'everyone.team', 'everyone.stack_size')
            ->join('player AS self', 'self.replayID', '=', 'replay.replayID')
            ->join('player AS everyone', 'everyone.replayID', '=', 'replay.replayID')
            ->where('self.blizz_id', $blizz_id)
            ->where('replay.region', $region)
            ->whereIn('game_type', $gameType)
            ->tap(function ($query) use ($season, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season, $startDate, $endDate);
            })
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('self.hero', $hero);
            })
            ->get();

        // Players per group size on each team, as 5-stack, 4, 3, 2, solo.
        $games = [];
        foreach ($rows as $row) {
            $game = &$games[$row->replayID];
            $game ??= [
                'self_team' => $row->self_team,
                'winner' => $row->winner,
                'teams' => [[0, 0, 0, 0, 0], [0, 0, 0, 0, 0]],
            ];

            // Old replays have no party data. Leaving those players out means
            // the team won't add up to five, so the game is skipped below.
            if ($row->stack_size === null || $row->stack_size === '') {
                unset($game);

                continue;
            }

            // A solo player is stored as stack_size 0 or 1.
            $size = max((int) $row->stack_size, 1);
            if ($size <= 5 && isset($game['teams'][$row->team])) {
                $game['teams'][$row->team][5 - $size]++;
            }
            unset($game);
        }
        unset($rows);

        $returnData = [];

        foreach ($games as $game) {
            $allyCombo = implode('', $game['teams'][$game['self_team']]);
            $enemyCombo = implode('', $game['teams'][1 - $game['self_team']]);

            // Games whose party sizes don't add up to a known setup are left out.
            $comboType = PartyCombinations::SECTIONS[$allyCombo] ?? null;
            if (! $comboType || ! isset(PartyCombinations::NAMES[$enemyCombo])) {
                continue;
            }

            $combo = $allyCombo.'|'.$enemyCombo;

            $returnData[$comboType][$combo] ??= [
                'ally_combo' => $allyCombo,
                'enemy_combo' => $enemyCombo,
                'wins' => 0,
                'losses' => 0,
                'win_rate' => 0,
                'stack_size_name' => PartyCombinations::name($enemyCombo),
            ];

            $returnData[$comboType][$combo][$game['winner'] == 1 ? 'wins' : 'losses']++;
        }

        // Same section order as the global endpoint, solo first.
        $returnData = array_filter(array_merge(array_fill_keys(PartyCombinations::SECTIONS, null), $returnData));

        foreach ($returnData as &$combos) {
            ksort($combos);
            foreach ($combos as &$combo) {
                $gamesPlayed = $combo['wins'] + $combo['losses'];
                $combo['win_rate'] = $gamesPlayed ? round(($combo['wins'] / $gamesPlayed) * 100, 2) : 0;
            }
            unset($combo);
        }
        unset($combos);

        PlayerStatsCache::updateOrCreate(
            ['params_hash' => $this->paramsHash($request)],
            [
                'blizz_id' => $request['blizz_id'],
                'region' => $request['region'],
                'latest_replayID' => $this->getLatestReplayId($request) ?? 0,
                'data' => json_encode($returnData),
            ]
        );

        return $returnData;
    }

    private function paramsHash(Request $request): string
    {
        return hash('sha256', json_encode(GlobalCacheKey::normalize([
            'page' => 'party',
            'blizz_id' => $request['blizz_id'],
            'region' => $request['region'],
            'game_type' => $request['game_type'],
            'season' => $request['season'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'hero' => $request['hero'],
            'game_map' => $request['game_map'],
        ])));
    }

    private function getLatestReplayId(Request $request): ?int
    {
        return $this->globalDataService->latestPlayerReplayId(
            $request['blizz_id'],
            $request['region'],
            $request['game_type'],
            $request['season'],
            $request['start_date'],
            $request['end_date']
        );
    }
}
