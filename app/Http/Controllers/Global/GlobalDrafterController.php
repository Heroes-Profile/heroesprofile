<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Global\Concerns\HandlesAsyncGlobalQueries;
use App\Models\Composition;
use App\Models\GlobalCompositions;
use App\Models\GlobalHeroDraftOrder;
use App\Models\GlobalHeroDraftOrderSummary;
use App\Models\MMRTypeID;
use App\Models\SeasonGameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GlobalDrafterController extends GlobalsInputValidationController
{
    use HandlesAsyncGlobalQueries;

    public const MINIMUM_PATCH = '2.52.0.81700';

    // Bump when the cached data changes shape or maths, so old entries are skipped.
    private const DRAFT_ORDER_CACHE_VERSION = 1;

    private const COMPOSITION_CACHE_VERSION = 2;

    public function show(Request $request)
    {
        return view('Drafter.drafter')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(true, self::MINIMUM_PATCH),
                'gametypedefault' => ['sl'],
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            ]);
    }

    public function getDraftOrderData(Request $request)
    {
        $validator = Validator::make($request->all(), $this->globalsValidationRules($request['timeframe_type'], $request['timeframe']));

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();
        $cacheKey = $this->globalCacheKey('DrafterDraftOrder', $gameVersionIDs, $request->all(), self::DRAFT_ORDER_CACHE_VERSION);

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeDraftOrderData');
    }

    /**
     * Pick counts for every hero at every draft position, keyed pick_number => hero_id => count.
     */
    public function executeDraftOrderData(Request $request)
    {
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();

        // The summary table has no hero level, hero rank or role rank, so those filters need the full table
        $advanced = ! empty($request['hero_level']) || ! empty($request['hero_league_tier']) || ! empty($request['role_league_tier']);

        $query = $advanced
            ? GlobalHeroDraftOrder::query()
                ->filterByHeroLeagueTier($request['hero_league_tier'])
                ->filterByRoleLeagueTier($request['role_league_tier'])
                ->filterByHeroLevel($request['hero_level'])
            : GlobalHeroDraftOrderSummary::query();

        $rows = $query
            ->select('hero', 'pick_number')
            ->selectRaw('SUM(count) as total')
            ->filterByGameVersion($gameVersionIDs)
            ->filterByGameType($this->globalDataService->getGameTypeFilterValues($request['game_type']))
            ->filterByLeagueTier($request['league_tier'])
            ->filterByGameMap($this->globalDataService->getGameMapFilterValues($request['game_map']))
            ->filterByRegion($this->globalDataService->getRegionFilterValues($request['region']))
            ->where('hero', '<>', 0)
            ->groupBy('hero', 'pick_number')
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $data[$row->pick_number][$row->hero] = (float) $row->total;
        }

        return $data;
    }

    public function getCompositionData(Request $request)
    {
        $validator = Validator::make($request->all(), $this->globalsValidationRules($request['timeframe_type'], $request['timeframe']));

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();
        $cacheKey = $this->globalCacheKey('DrafterComposition', $gameVersionIDs, $request->all(), self::COMPOSITION_CACHE_VERSION);

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeCompositionData');
    }

    /**
     * Every played composition with its role names, total games and games per hero,
     * keyed composition_id => ['roles', 'games', 'heroes' => hero_id => games].
     */
    public function executeCompositionData(Request $request)
    {
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();

        $rows = GlobalCompositions::query()
            ->select('composition_id', 'hero')
            ->selectRaw('SUM(games_played) as games_played')
            ->filterByGameVersion($gameVersionIDs)
            ->filterByGameType($this->globalDataService->getGameTypeFilterValues($request['game_type']))
            ->filterByLeagueTier($request['league_tier'])
            ->filterByHeroLeagueTier($request['hero_league_tier'])
            ->filterByRoleLeagueTier($request['role_league_tier'])
            ->filterByGameMap($this->globalDataService->getGameMapFilterValues($request['game_map']))
            ->filterByHeroLevel($request['hero_level'])
            ->excludeMirror($request['mirror'])
            ->filterByRegion($this->globalDataService->getRegionFilterValues($request['region']))
            ->groupBy('composition_id', 'hero')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $roleNames = MMRTypeID::all()->pluck('name', 'mmr_type_id');
        $compositions = Composition::whereIn('composition_id', $rows->pluck('composition_id')->unique())->get()->keyBy('composition_id');

        $data = [];
        foreach ($rows as $row) {
            $composition = $compositions[$row->composition_id] ?? null;
            if (! $composition) {
                continue;
            }

            if (! isset($data[$row->composition_id])) {
                $data[$row->composition_id] = [
                    'roles' => collect([$composition->role_one, $composition->role_two, $composition->role_three, $composition->role_four, $composition->role_five])
                        ->map(fn ($roleID) => $roleNames[$roleID] ?? null)
                        ->filter()
                        ->values()
                        ->toArray(),
                    'games' => 0,
                    'heroes' => [],
                ];
            }

            $data[$row->composition_id]['games'] += (float) $row->games_played;
            if ($row->hero) {
                $data[$row->composition_id]['heroes'][$row->hero] = (float) $row->games_played;
            }
        }

        return $data;
    }
}
