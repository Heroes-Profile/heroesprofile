<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Api\External\Concerns\TranslatesInternalFailures;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tools\ActivityGraphsController;
use App\Http\Controllers\Tools\RandomizeMeController;
use App\Support\ApiParameters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tools. Scoped to neither a patch nor a player, so none of the globals
 * parameters apply.
 */
class ToolsController extends Controller
{
    use TranslatesInternalFailures;

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

        if ($failure = $this->internalFailure($result)) {
            return $failure;
        }

        return $result instanceof Response ? $result : response()->json($result);
    }

    /**
     * Unique players seen per month, optionally filtered by `game_type` and
     * `region`. Both are optional and absence means everything. One of each, as
     * the site's own page offers.
     */
    public function uniquePlayers(Request $request): Response
    {
        // The controller looks both up by exact key: a display name or a region id
        // reaches it as an undefined index, and an unknown game type as no filter.
        foreach (['game_type', 'region'] as $parameter) {
            if (! $request->filled($parameter)) {
                continue;
            }

            [$resolved, $unknown] = $parameter === 'game_type'
                ? ApiParameters::gameTypes($request->input($parameter))
                : ApiParameters::regionNames($request->input($parameter));

            if ($unknown !== []) {
                return $this->error('unknown_'.$parameter, 'Not a recognised '.str_replace('_', ' ', $parameter).': '.implode(', ', $unknown).'.');
            }

            if (count($resolved) !== 1) {
                return $this->error('single_'.$parameter.'_only', 'This endpoint takes one '.str_replace('_', ' ', $parameter).'.');
            }

            $request->merge([$parameter => $resolved[0]]);
        }

        $result = app()->call(
            [app(ActivityGraphsController::class), 'getUniquePlayersPerMonth'],
            ['request' => $request]
        );

        if ($failure = $this->internalFailure($result)) {
            return $failure;
        }

        return $result instanceof Response ? $result : response()->json($result);
    }

    private function error(string $code, string $message): JsonResponse
    {
        return response()->json([
            'error' => ['code' => $code, 'message' => $message],
        ], 422);
    }
}
