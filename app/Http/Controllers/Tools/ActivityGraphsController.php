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
        $currentMonth = $now->format('Y-m');

        $pastMonths = $cache->get($allCacheKey);

        if ($pastMonths === null) {
            $pastMonths = $this->buildPastMonthsCache($cache, $now, $currentMonth, $gameType, $region, $filterHash);
            $cache->forever($allCacheKey, $pastMonths);
        }

        $currentCount = $this->queryUniquePlayersForMonth($now, $gameType, $region);

        $result = $pastMonths;
        $result[] = [
            'x_label' => $currentMonth,
            'unique_players' => $currentCount,
        ];

        return response()->json($result);
    }

    private function buildPastMonthsCache($cache, Carbon $now, string $currentMonth, ?array $gameType, ?array $region, string $filterHash): array
    {
        $start = Carbon::parse(self::START_DATE)->startOfMonth();
        $result = [];

        $month = $start->copy();
        while ($month->format('Y-m') !== $currentMonth) {
            $monthKey = $month->format('Y-m');
            $cacheKey = 'ActivityGraph|UniquePlayersByMonth|'.$monthKey.'|'.$filterHash;

            $count = $cache->rememberForever($cacheKey, function () use ($month, $gameType, $region) {
                return $this->queryUniquePlayersForMonth($month, $gameType, $region);
            });

            $result[] = [
                'x_label' => $monthKey,
                'unique_players' => $count,
            ];

            $month->addMonth();
        }

        return $result;
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
