<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\RoleInputValidation;
use App\Services\GlobalDataService;
use App\Support\CsvResponse;
use Illuminate\Http\Request;

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

    public function __construct(private readonly GlobalDataService $globalDataService) {}

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

        $heroes = $heroes->values();

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
