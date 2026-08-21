<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Player\PlayerController as InternalPlayerController;
use App\Http\Controllers\Player\PlayerHeroesMapsRolesController;
use App\Http\Controllers\Player\PlayerMatchHistory;
use App\Http\Controllers\Player\PlayerMMRController;
use App\Http\Controllers\Player\PlayerTalentsController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Player endpoints, delegating to the controllers the site itself uses.
 *
 * Callers identify a player by `battletag` and `region`, the same pair the old
 * API took — a player knows their battletag and has never seen a blizz_id. The
 * id is resolved here and passed down, because every internal controller wants
 * all three.
 *
 * These controllers vary their output on Auth::check() for owner-only fields.
 * The public API is stateless, so that check is always false and callers get the
 * anonymous shape, which is what v1 documents.
 *
 * They also disagree with each other on two parameters: `hero` is a name to some
 * and an id to others, and `game_type` is a string to some and an array to
 * others. The public contract is one of each — `hero` is always a name and
 * `game_type` is always comma-separated short names — and each call declares
 * what its target actually wants.
 */
class PlayerController extends Controller
{
    public function profile(Request $request): Response
    {
        return $this->delegate($request, InternalPlayerController::class, 'getPlayerData');
    }

    public function matches(Request $request): Response
    {
        return $this->delegate($request, PlayerMatchHistory::class, 'getData', [
            'pagination_page' => 1,
        ], ['hero' => 'id', 'game_type' => 'array']);
    }

    public function heroes(Request $request): Response
    {
        return $this->delegate($request, PlayerHeroesMapsRolesController::class, 'getData', [
            'page' => 'hero',
            'type' => 'all',
        ], ['game_type' => 'array']);
    }

    public function mmr(Request $request): Response
    {
        return $this->delegate($request, PlayerMMRController::class, 'getData', [
            'type' => 'Player',
        ], ['hero' => 'id']);
    }

    public function talentBuild(Request $request): Response
    {
        return $this->delegate($request, PlayerTalentsController::class, 'getPlayerTalentData', [], [
            'game_type' => 'array',
        ]);
    }

    /**
     * @param  array<string, mixed>  $defaults  Internal parameters the site's own
     *                                          pages always send, defaulted here so
     *                                          a public caller need not know them.
     */
    private function delegate(
        Request $request,
        string $controller,
        string $method,
        array $defaults = [],
        array $expects = []
    ): Response {
        $validated = $request->validate([
            'battletag' => ['required', 'string'],
            'region' => ['required', 'integer', 'in:1,2,3,5'],
        ]);

        $blizzId = $this->globalDataService->getBlizzIDGivenFullBattletag(
            $validated['battletag'],
            $validated['region']
        );

        if ($blizzId === null) {
            return $this->error(
                'player_not_found',
                'No player found for that battletag and region.',
                404
            );
        }

        if ($this->globalDataService->isRestrictedAccount($blizzId, $validated['region'])) {
            return $this->error(
                'player_unavailable',
                'That player has made their profile private.',
                403
            );
        }

        foreach ($defaults as $key => $value) {
            if (! $request->has($key)) {
                $request->merge([$key => $value]);
            }
        }

        $request->merge(['blizz_id' => $blizzId]);

        if (($expects['hero'] ?? null) === 'id' && $request->filled('hero')) {
            $heroId = $this->globalDataService->getHeroes()
                ->firstWhere('name', $request->input('hero'))?->id;

            if ($heroId === null) {
                return $this->error('unknown_hero', 'No hero by that name.', 422);
            }

            $request->merge(['hero' => $heroId]);
        }

        if (($expects['game_type'] ?? null) === 'array' && $request->filled('game_type')) {
            $request->merge([
                'game_type' => explode(',', (string) $request->input('game_type')),
            ]);
        }

        $result = app()->call([app($controller), $method], ['request' => $request]);

        // Some of these return an array, others a response object. Wrapping a
        // response in response()->json() serialises the object itself, so the
        // caller receives {"headers":…,"original":…} instead of the payload.
        if ($result instanceof Response) {
            return $result;
        }

        return response()->json($result);
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }
}
