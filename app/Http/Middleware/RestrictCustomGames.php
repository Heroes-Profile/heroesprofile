<?php

namespace App\Http\Middleware;

use App\Services\GlobalDataService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Custom games in player data follow the same rule as custom match pages: only the
 * signed-in owner who has turned the custom games setting on sees them. The pages
 * never offer them to anyone else, but the data routes took `game_type=cu` from
 * anyone.
 *
 * ->middleware('restrictCustomGames')        owner-scoped (blizz_id/region in the request)
 * ->middleware('restrictCustomGames:never')  no owner, custom games never served
 */
class RestrictCustomGames
{
    private const CUSTOM = 'cu';

    public function handle(Request $request, Closure $next, string $scope = 'owner'): Response
    {
        if (! $request->filled('game_type')) {
            return $next($request);
        }

        $input = $request->input('game_type');
        $values = is_array($input) ? $input : explode(',', (string) $input);

        $kept = array_values(array_filter(
            $values,
            fn ($value) => strtolower(trim((string) $value)) !== self::CUSTOM
        ));

        if (count($kept) === count($values)) {
            return $next($request);
        }

        $ownerOptedIn = $scope === 'owner' && app(GlobalDataService::class)
            ->showcustomgames(null, $request->input('blizz_id'), $request->input('region'));

        if ($ownerOptedIn) {
            return $next($request);
        }

        // Asked for custom games only: answering with every other type instead would be
        // a different question.
        if ($kept === []) {
            return response()->json([
                'errors' => ['Custom games are not available.'],
                'status' => 'failure to validate inputs',
            ]);
        }

        $request->merge(['game_type' => is_array($input) ? $kept : implode(',', $kept)]);

        return $next($request);
    }
}
