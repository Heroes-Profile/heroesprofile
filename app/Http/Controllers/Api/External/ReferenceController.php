<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\RoleInputValidation;
use App\Support\CsvResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Reference data: the lookup tables the rest of the API is described in terms of.
 *
 * Every method is a thin wrapper over GlobalDataService, which owns the queries
 * and the caching. Nothing here queries anything directly — if a lookup is needed
 * that the service does not already expose, it belongs in the service.
 */
class ReferenceController extends Controller
{
    /** Overall rank tiers rather than per-role or per-hero, as the old API did. */
    private const OVERALL_TIER_TYPE = 10000;

    /**
     * Columns on `heroes` that are not reliable enough to publish. They are kept
     * in the table and still available to the site, which treats them as hints
     * rather than facts — an API caller has no such context and would read them
     * as authoritative.
     */
    private const WITHHELD_HERO_FIELDS = [
        'rework_date',
        'last_change_patch_version',
        'last_updated',
    ];

    public function maps(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['sometimes', 'in:json,csv'],
        ]);

        $maps = $this->globalDataService->getMaps()->values();

        if (($validated['mode'] ?? 'json') === 'csv') {
            return CsvResponse::stream($maps->toArray(), 'maps');
        }

        return response()->json(['maps' => $maps]);
    }

    /**
     * Filtered in PHP against the cached hero list rather than in SQL, so a
     * filtered call costs no more than an unfiltered one.
     */
    public function heroes(Request $request)
    {
        $validated = $request->validate([
            'hero' => ['sometimes', new HeroInputValidation],
            'role' => ['sometimes', new RoleInputValidation],
            'mode' => ['sometimes', 'in:json,csv'],
        ]);

        $heroes = $this->globalDataService->getHeroes();

        if (isset($validated['hero'])) {
            $heroes = $heroes->filter(fn ($hero) => $hero->name === $validated['hero']
                || $hero->short_name === $validated['hero']);
        }

        if (isset($validated['role'])) {
            $heroes = $heroes->where('new_role', $validated['role']);
        }

        // Mapped to arrays rather than `makeHidden()`: getHeroes() returns a shallow
        // clone of a cached collection, so hiding attributes would mutate the models
        // the site's own pages are using for the rest of the request.
        $heroes = $heroes->values()->map(
            fn ($hero) => Arr::except($hero->toArray(), self::WITHHELD_HERO_FIELDS)
        );

        if (($validated['mode'] ?? 'json') === 'csv') {
            return CsvResponse::stream($heroes->toArray(), 'heroes');
        }

        return response()->json(['heroes' => $heroes]);
    }

    /** Grouped by hero, matching how the old API returned them. */
    public function talents(Request $request)
    {
        $validated = $request->validate([
            'hero' => ['sometimes', new HeroInputValidation],
        ]);

        return response()->json([
            'talents' => $this->globalDataService->getPlayableHeroesTalents($validated['hero'] ?? null),
        ]);
    }

    public function patches()
    {
        return response()->json([
            'patches' => $this->globalDataService->getPatches(),
        ]);
    }

    /**
     * Resolves an MMR to its rank tier. Both halves are cached — the breakdown by
     * getRankTiers(), the naming by calculateSubTier().
     */
    public function mmrTier(Request $request)
    {
        $validated = $request->validate([
            'game_type' => ['required', new GameTypeInputValidation],
            'mmr' => ['required', 'numeric', 'min:0'],
        ]);

        $gameTypeId = $this->globalDataService->getGameTypeFilterValues($validated['game_type']);
        $rankTiers = $this->globalDataService->getRankTiers($gameTypeId, self::OVERALL_TIER_TYPE);

        return response()->json([
            'game_type' => $validated['game_type'],
            'mmr' => (int) $validated['mmr'],
            'tier' => $this->globalDataService->calculateSubTier($rankTiers, $validated['mmr']),
        ]);
    }
}
