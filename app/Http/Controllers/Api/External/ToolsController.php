<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\ActivityGraphsController;
use App\Http\Controllers\Tools\RandomizeMeController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tools. Scoped to neither a patch nor a player, so none of the globals
 * parameters apply.
 */
class ToolsController extends Controller
{
    /**
     * A random talent build for one hero.
     *
     * `hero` is checked here because the internal controller answers a miss with
     * 200 and a `status` field rather than an error status.
     */
    public function randomize(Request $request): Response
    {
        if (! $request->filled('hero')) {
            return response()->json([
                'error' => [
                    'code' => 'missing_hero',
                    'message' => 'This endpoint needs a hero to build for.',
                ],
            ], 422);
        }

        $result = app()->call(
            [app(RandomizeMeController::class), 'getRandomBuild'],
            ['request' => $request]
        );

        return $result instanceof Response ? $result : response()->json($result);
    }

    /**
     * Unique players seen per month, optionally filtered by `game_type` and
     * `region`. Both are optional and absence means everything.
     */
    public function uniquePlayers(Request $request): Response
    {
        $result = app()->call(
            [app(ActivityGraphsController::class), 'getUniquePlayersPerMonth'],
            ['request' => $request]
        );

        return $result instanceof Response ? $result : response()->json($result);
    }
}
