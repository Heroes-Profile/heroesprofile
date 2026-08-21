<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\GlobalDataService;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    /**
     * Playable maps, straight off the `global_maps` cache the site already keeps
     * warm — no query of its own.
     *
     * The old API returned every row of `maps` including unplayable ones and a
     * bare array. This returns playable maps in an envelope, which is where the
     * test-data note goes when fixtures are being served.
     */
    public function index(GlobalDataService $globalDataService): JsonResponse
    {
        return response()->json([
            'maps' => $globalDataService->getMaps()->values(),
        ]);
    }
}
