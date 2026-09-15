<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\GameType;
use App\Models\Map;
use App\Models\PlayerStatsCache;
use App\Rules\DateInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use App\Services\GlobalQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerAwardsController extends Controller
{
    private const CACHE_TTL_SECONDS = 1200;

    // Replays before this have no match awards recorded
    private const AWARD_TRACKING_REPLAY_ID = 10355545;

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

        return view('Player.awards')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
        ]);
    }

    private function validationRules(): array
    {
        return [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['required', new GameTypeInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation],
            'start_date' => ['sometimes', 'nullable', new DateInputValidation],
            'end_date' => ['sometimes', 'nullable', new DateInputValidation],
        ];
    }

    /**
     * Every game with one award under the same filters, newest first.
     */
    public function getAwardGames(Request $request)
    {
        $validator = Validator::make($request->all(), array_merge($this->validationRules(), [
            'award_id' => 'required|integer',
            'pagination_page' => 'required|integer|min:1',
        ]));

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $lookups = $this->rowLookups();

        return $this->baseQuery($request)
            ->where('scores.match_award', $request['award_id'])
            ->select('replay.replayID', 'replay.game_date', 'replay.game_type', 'replay.game_map', 'player.hero', 'player.winner', 'scores.match_award')
            ->orderByDesc('replay.replayID')
            ->paginate(100, ['*'], 'page', $request['pagination_page'])
            ->through(fn ($row) => $this->formatAwardRow($row, $lookups));
    }

    public function getData(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        if (config('app.env') !== 'production') {
            return response()->json($this->executeGetData($request));
        }

        $paramsHash = $this->paramsHash($request);

        $dbCache = PlayerStatsCache::where('params_hash', $paramsHash)->first();

        if ($dbCache) {
            $latestReplayId = $this->getLatestReplayId($request);

            if (! $latestReplayId || $dbCache->latest_replayID >= $latestReplayId) {
                return response()->json(json_decode($dbCache->data, true));
            }
        }

        return app(GlobalQueryService::class)->dispatchAsync(
            'PlayerAwardsData|'.$paramsHash,
            static::class,
            'executeGetData',
            $request->all(),
            self::CACHE_TTL_SECONDS
        );
    }

    public function executeGetData(Request $request)
    {
        $lookups = $this->rowLookups();
        $awards = $lookups['awards'];

        $totalGames = $this->baseQuery($request)->count();

        $awardCounts = $this->baseQuery($request)
            ->whereIn('scores.match_award', $awards->keys()->all())
            ->groupBy('scores.match_award')
            ->select('scores.match_award', DB::raw('COUNT(*) AS total'))
            ->pluck('total', 'match_award');

        $awardTotals = $awards->map(function ($award) use ($awardCounts, $totalGames) {
            $count = (int) ($awardCounts[$award->award_id] ?? 0);

            return [
                'award_id' => $award->award_id,
                'title' => $award->title,
                'icon' => $award->icon,
                'description' => $award->description,
                'count' => $count,
                'rate' => $totalGames > 0 ? round(($count / $totalGames) * 100, 2) : 0,
            ];
        })->sortByDesc('count')->values();

        $recentAwards = $this->baseQuery($request)
            ->whereIn('scores.match_award', $awards->keys()->all())
            ->select('replay.replayID', 'replay.game_date', 'replay.game_type', 'replay.game_map', 'player.hero', 'player.winner', 'scores.match_award')
            ->orderByDesc('replay.replayID')
            ->limit(5)
            ->get()
            ->map(fn ($row) => $this->formatAwardRow($row, $lookups));

        $returnData = [
            'total_games' => $totalGames,
            'awards' => $awardTotals,
            'recent_awards' => $recentAwards,
        ];

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

    private function rowLookups(): array
    {
        return [
            'game_types' => $this->globalDataService->getGameTypeIDtoString(),
            'maps' => Map::all()->keyBy('map_id'),
            'heroes' => $this->globalDataService->getHeroes()->keyBy('id'),
            'awards' => Award::all()->keyBy('award_id'),
        ];
    }

    private function formatAwardRow($row, array $lookups): array
    {
        return [
            'replayID' => $row->replayID,
            'game_date' => $row->game_date,
            'game_type' => $lookups['game_types'][$row->game_type],
            'game_map' => $lookups['maps'][$row->game_map],
            'hero' => $lookups['heroes'][$row->hero],
            'winner' => $row->winner,
            'award' => $lookups['awards'][$row->match_award],
        ];
    }

    private function baseQuery(Request $request)
    {
        $gameType = GameType::whereIn('short_name', (array) $request['game_type'])->pluck('type_id')->toArray();
        $gameMap = $request['game_map'] ? Map::whereIn('name', (array) $request['game_map'])->pluck('map_id')->toArray() : null;
        $hero = $request['hero'];
        $role = $request['role'];

        return DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->join('heroes', 'heroes.id', '=', 'player.hero')
            ->where('player.blizz_id', $request['blizz_id'])
            ->where('replay.region', $request['region'])
            ->where('replay.replayID', '>', self::AWARD_TRACKING_REPLAY_ID)
            ->whereIn('replay.game_type', $gameType)
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('player.hero', $hero);
            })
            ->when(! is_null($role), function ($query) use ($role) {
                return $query->where('heroes.new_role', $role);
            })
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('replay.game_map', $gameMap);
            })
            ->tap(function ($query) use ($request) {
                $this->globalDataService->applySeasonsOrDateRange($query, $request['season'], $request['start_date'], $request['end_date']);
            });
    }

    private function paramsHash(Request $request): string
    {
        return hash('sha256', json_encode([
            'page' => 'awards',
            'blizz_id' => $request['blizz_id'],
            'region' => $request['region'],
            'game_type' => $request['game_type'],
            'hero' => $request['hero'],
            'role' => $request['role'],
            'game_map' => $request['game_map'],
            'season' => $request['season'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
        ]));
    }

    private function getLatestReplayId(Request $request): ?int
    {
        $gameTypeIds = GameType::whereIn('short_name', (array) $request['game_type'])->pluck('type_id')->toArray();

        return DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $request['blizz_id'])
            ->where('replay.region', $request['region'])
            ->whereIn('game_type', $gameTypeIds)
            ->tap(function ($query) use ($request) {
                $this->globalDataService->applySeasonsOrDateRange($query, $request['season'], $request['start_date'], $request['end_date']);
            })
            ->max('replay.replayID');
    }
}
