<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Global\GlobalsInputValidationController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActivityGraphsController extends GlobalsInputValidationController
{
    private const START_DATE = '2014-10-01';

    // A month is only final once we are this far past its end - replays for it
    // can still arrive late. Until then it gets queried live.
    private const SETTLE_DAYS = 7;

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
        $gameTypeRaw = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $gameType = is_null($gameTypeRaw) ? null : (array) $gameTypeRaw;

        $regionRaw = $this->globalDataService->getRegionFilterValues($request['region']);
        $region = is_null($regionRaw) ? null : (array) $regionRaw;

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
            $result[] = [
                'x_label' => $month->format('Y-m'),
                'unique_players' => $this->queryUniquePlayersForMonth($month, $gameType, $region),
            ];

            $month->addMonth();
        }

        return response()->json($result);
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
