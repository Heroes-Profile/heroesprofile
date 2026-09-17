<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Global\GlobalsInputValidationController;
use App\Rules\GameTypeInputValidation;
use App\Rules\RegionInputValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActivityGraphsController extends GlobalsInputValidationController
{
    private const START_DATE = '2014-10-01';

    // A month is only final once we are this far past its end - replays for it
    // can still arrive late. Until then it gets queried live.
    private const SETTLE_DAYS = 7;

    // Unsettled months are still moving, but not by the minute.
    private const LIVE_CACHE_SECONDS = 1800;

    public function show(Request $request)
    {
        return view('Tools.activityGraphs')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
            ]);
    }

    public function getUniquePlayersPerMonth(Request $request)
    {
        $input = array_filter(
            $request->only(['game_type', 'region']),
            fn ($value) => $value !== null && $value !== '' && $value !== []
        );

        $validator = Validator::make($input, [
            'game_type' => ['sometimes', new GameTypeInputValidation],
            'region' => ['sometimes', new RegionInputValidation],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        // Sorted and deduplicated so the same selection always hashes to the same key.
        $gameType = isset($input['game_type'])
            ? $this->normalizedIds($this->globalDataService->getGameTypeFilterValues(is_array($input['game_type']) ? $input['game_type'] : explode(',', $input['game_type'])))
            : null;

        $region = isset($input['region'])
            ? $this->normalizedIds(array_values(array_intersect_key(
                $this->globalDataService->getRegionStringToID(),
                array_flip(is_array($input['region']) ? $input['region'] : explode(',', $input['region']))
            )))
            : null;

        $filterHash = hash('sha256', json_encode(['game_type' => $gameType, 'region' => $region]));
        $allCacheKey = 'ActivityGraph|UniquePlayersByMonth|All|'.$filterHash;

        $cache = Cache::store('database');
        $now = Carbon::now();
        $firstUnsettled = $now->copy()->subDays(self::SETTLE_DAYS)->startOfMonth();

        $cached = $cache->get($allCacheKey, []);
        $settled = $this->appendSettledMonths($cache, $cached, $firstUnsettled, $gameType, $region, $filterHash);

        if (count($settled) !== count($cached)) {
            $cache->forever($allCacheKey, $settled);
        }

        $result = $settled;

        $month = $firstUnsettled->copy();
        while ($month->lessThanOrEqualTo($now)) {
            $monthKey = $month->format('Y-m');

            $result[] = [
                'x_label' => $monthKey,
                'unique_players' => $cache->remember(
                    'ActivityGraph|UniquePlayersByMonth|Live|'.$monthKey.'|'.$filterHash,
                    self::LIVE_CACHE_SECONDS,
                    fn () => $this->queryUniquePlayersForMonth($month, $gameType, $region)
                ),
            ];

            $month->addMonth();
        }

        return response()->json($result);
    }

    private function normalizedIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);

        return $ids;
    }

    private function appendSettledMonths($cache, array $settled, Carbon $firstUnsettled, ?array $gameType, ?array $region, string $filterHash): array
    {
        $cutoff = $firstUnsettled->format('Y-m');
        $settled = array_values(array_filter($settled, fn ($row) => $row['x_label'] < $cutoff));

        $month = empty($settled)
            ? Carbon::parse(self::START_DATE)->startOfMonth()
            : Carbon::parse(end($settled)['x_label'].'-01')->startOfMonth()->addMonth();

        while ($month->lessThan($firstUnsettled)) {
            $monthKey = $month->format('Y-m');
            $cacheKey = 'ActivityGraph|UniquePlayersByMonth|'.$monthKey.'|'.$filterHash;

            $count = $cache->rememberForever($cacheKey, function () use ($month, $gameType, $region) {
                return $this->queryUniquePlayersForMonth($month, $gameType, $region);
            });

            $settled[] = [
                'x_label' => $monthKey,
                'unique_players' => $count,
            ];

            $month->addMonth();
        }

        return $settled;
    }

    private function queryUniquePlayersForMonth(Carbon $month, ?array $gameType, ?array $region): int
    {
        $start = $month->copy()->startOfMonth()->toDateTimeString();
        $end = $month->copy()->endOfMonth()->toDateTimeString();

        return DB::connection('heroesprofile')
            ->table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->whereBetween('replay.game_date', [$start, $end])
            ->when(! is_null($gameType), fn ($q) => $q->whereIn('replay.game_type', $gameType))
            ->when(! is_null($region), fn ($q) => $q->whereIn('replay.region', $region))
            ->distinct()
            ->count('player.battletag');
    }
}
