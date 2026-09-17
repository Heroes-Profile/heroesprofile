<?php

namespace App\Http\Middleware;

use App\Services\GlobalDataService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The data routes behind player pages, held to the same rule as the pages
 * themselves (CheckIfPrivateProfilePage): banned accounts are refused, and private
 * ones unless the signed-in user owns them. Those routes take `blizz_id` directly,
 * so the page check alone left them open to anyone posting an id.
 *
 * Checks top-level `blizz_id`/`region` by default. Pass the keys of nested
 * players instead where a route takes several:
 * ->middleware('checkIfPrivateProfileData:player1,player2')
 */
class CheckIfPrivateProfileData
{
    public function handle(Request $request, Closure $next, string ...$playerKeys): Response
    {
        $service = app(GlobalDataService::class);
        $user = Auth::user();

        foreach ($this->accounts($request, $playerKeys) as [$blizzId, $region]) {
            if ($service->isHiddenFrom($blizzId, $region, $user)) {
                return response()->json(['status' => 'private'], 403);
            }
        }

        return $next($request);
    }

    /** @return array<int, array{0: mixed, 1: mixed}> */
    private function accounts(Request $request, array $playerKeys): array
    {
        if ($playerKeys === []) {
            return $request->filled('blizz_id') ? [[$request->input('blizz_id'), $request->input('region')]] : [];
        }

        $accounts = [];

        foreach ($playerKeys as $key) {
            $player = $request->input($key);

            if (is_array($player) && isset($player['blizz_id'])) {
                $accounts[] = [$player['blizz_id'], $player['region'] ?? null];
            }
        }

        return $accounts;
    }
}
