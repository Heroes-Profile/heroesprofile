<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Global\Concerns\HandlesAsyncGlobalQueries;
use App\Models\Composition;
use App\Models\GlobalCompositions;
use App\Models\GlobalHeroDraftOrder;
use App\Models\MMRTypeID;
use App\Models\SeasonGameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GlobalDrafterController extends GlobalsInputValidationController
{
    use HandlesAsyncGlobalQueries;

    public function show(Request $request)
    {
        return view('Drafter.drafter')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
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
        $cacheKey = $this->globalCacheKey('DrafterDraftOrder', $gameVersionIDs, $request->all());

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeDraftOrderData');
    }

    /**
     * Pick counts for every hero at every draft position, keyed pick_number => hero_id => count.
     */
    public function executeDraftOrderData(Request $request)
    {
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();

        $rows = GlobalHeroDraftOrder::query()
            ->select('hero', 'pick_number')
            ->selectRaw('SUM(count) as total')
            ->filterByGameVersion($gameVersionIDs)
            ->filterByGameType($this->globalDataService->getGameTypeFilterValues($request['game_type']))
            ->filterByLeagueTier($request['league_tier'])
            ->filterByHeroLeagueTier($request['hero_league_tier'])
            ->filterByRoleLeagueTier($request['role_league_tier'])
            ->filterByGameMap($this->globalDataService->getGameMapFilterValues($request['game_map']))
            ->filterByHeroLevel($request['hero_level'])
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
        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'team_picks' => 'required|array|min:1|max:5',
            'team_picks.*' => 'integer',
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();

        // Only the roles picked so far change the answer, so key on those rather than the heroes
        $keyParameters = $request->except('team_picks');
        $keyParameters['team_roles'] = $this->getTeamRoleIDs($request['team_picks']);
        $cacheKey = $this->globalCacheKey('DrafterComposition', $gameVersionIDs, $keyParameters);

        return $this->asyncGlobalResponse($request, $cacheKey, $gameVersion, 'executeCompositionData');
    }

    /**
     * Heroes from the most played composition containing the team's current roles,
     * with games played zeroed for roles the team has already filled.
     */
    public function executeCompositionData(Request $request)
    {
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameVersionIDs = SeasonGameVersion::whereIn('game_version', $gameVersion)->pluck('id')->toArray();
        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $region = $this->globalDataService->getRegionFilterValues($request['region']);

        $teamRoles = $this->getTeamRoleIDs($request['team_picks']);

        $validCompositionIDs = Composition::query()
            ->whereRaw('CONCAT(role_one, role_two, role_three, role_four, role_five) LIKE ?', ['%'.implode('%', $teamRoles).'%'])
            ->pluck('composition_id')
            ->toArray();

        if (empty($validCompositionIDs)) {
            return [];
        }

        $filtered = function () use ($gameVersionIDs, $gameType, $gameMap, $region, $request) {
            return GlobalCompositions::query()
                ->filterByGameVersion($gameVersionIDs)
                ->filterByGameType($gameType)
                ->filterByLeagueTier($request['league_tier'])
                ->filterByHeroLeagueTier($request['hero_league_tier'])
                ->filterByRoleLeagueTier($request['role_league_tier'])
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($request['hero_level'])
                ->excludeMirror($request['mirror'])
                ->filterByRegion($region);
        };

        $compositionID = $filtered()
            ->select('composition_id')
            ->selectRaw('SUM(games_played) as games_played')
            ->whereIn('composition_id', $validCompositionIDs)
            ->groupBy('composition_id')
            ->orderByDesc('games_played')
            ->value('composition_id');

        if (! $compositionID) {
            return [];
        }

        $composition = Composition::find($compositionID);
        $remainingRoles = array_count_values([
            $composition->role_one, $composition->role_two, $composition->role_three, $composition->role_four, $composition->role_five,
        ]);
        foreach ($teamRoles as $roleID) {
            $remainingRoles[$roleID] = ($remainingRoles[$roleID] ?? 0) - 1;
        }

        $roleIDsByName = MMRTypeID::all()->pluck('mmr_type_id', 'name');
        $heroRoleIDs = $this->globalDataService->getHeroes()->mapWithKeys(fn ($hero) => [$hero->id => $roleIDsByName[$hero->new_role] ?? null]);

        return $filtered()
            ->select('hero')
            ->selectRaw('SUM(games_played) as games_played')
            ->where('composition_id', $compositionID)
            ->groupBy('hero')
            ->get()
            ->filter(fn ($row) => isset($heroRoleIDs[$row->hero]))
            ->map(function ($row) use ($heroRoleIDs, $remainingRoles) {
                $roleID = $heroRoleIDs[$row->hero];

                return [
                    'hero_id' => $row->hero,
                    'games_played' => ($remainingRoles[$roleID] ?? 0) != 0 ? (float) $row->games_played : 0,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getTeamRoleIDs($teamPicks)
    {
        $roleIDsByName = MMRTypeID::all()->pluck('mmr_type_id', 'name');
        $heroes = $this->globalDataService->getHeroes()->keyBy('id');

        $roleIDs = collect($teamPicks)
            ->map(fn ($heroID) => isset($heroes[$heroID]) ? ($roleIDsByName[$heroes[$heroID]->new_role] ?? null) : null)
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        return $roleIDs;
    }
}
