<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerController as InternalPlayerController;
use App\Http\Controllers\Player\PlayerHeroesMapsRolesController;
use App\Http\Controllers\Player\PlayerMatchHistory;
use App\Http\Controllers\Player\PlayerMatchupsController;
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
    /**
     * What the site itself sends for a visitor who is not logged in — see
     * `GlobalDataService::getGameTypeDefault()`. The public API is always
     * anonymous, so this is always the answer.
     */
    private const DEFAULT_GAME_TYPE = 'sl';

    /** Public parameter name => what the internal controller calls it. */
    private const SUBJECTS = [
        'hero' => 'hero',
        'map' => 'game_map',
        'role' => 'role',
    ];

    public function profile(Request $request): Response
    {
        return $this->delegate($request, InternalPlayerController::class, 'getPlayerData');
    }

    public function matches(Request $request): Response
    {
        return $this->delegate($request, PlayerMatchHistory::class, 'getData', [
            'pagination_page' => 1,
            'game_type' => self::DEFAULT_GAME_TYPE,
        ], ['hero' => 'id', 'game_type' => 'array']);
    }

    public function heroes(Request $request): Response
    {
        return $this->breakdown($request, 'hero', 'all');
    }

    public function hero(Request $request): Response
    {
        return $this->breakdown($request, 'hero', 'single');
    }

    public function maps(Request $request): Response
    {
        return $this->breakdown($request, 'map', 'all');
    }

    public function map(Request $request): Response
    {
        return $this->breakdown($request, 'map', 'single');
    }

    public function roles(Request $request): Response
    {
        return $this->breakdown($request, 'role', 'all');
    }

    public function role(Request $request): Response
    {
        return $this->breakdown($request, 'role', 'single');
    }

    /**
     * Who this player meets most, and how they fare against them.
     *
     * `game_type` is deliberately not defaulted here: the controller reads its
     * absence as every game type, which is the more useful answer.
     */
    public function matchups(Request $request): Response
    {
        return $this->delegate($request, PlayerMatchupsController::class, 'getMatchupData', [], [
            'hero' => 'id',
            'game_type' => 'array',
        ]);
    }

    /** Team-mates and opponents this player sees repeatedly. */
    public function friendFoe(Request $request): Response
    {
        return $this->delegate($request, FriendFoeController::class, 'getFriendFoeData', [
            // Absent, the controller reaches a count() on a null. The site's own
            // page always sends one.
            'type' => 'friend',
            // Required here, unlike `players/matchups`, which reads its absence as
            // every game type. This one puts it straight into a whereIn().
            'game_type' => self::DEFAULT_GAME_TYPE,
        ], [
            'hero' => 'id',
            'game_type' => 'array',
        ]);
    }

    public function mmr(Request $request): Response
    {
        return $this->ratings($request, 'Player');
    }

    public function heroMmr(Request $request): Response
    {
        return $this->ratings($request, 'Hero');
    }

    public function roleMmr(Request $request): Response
    {
        return $this->ratings($request, 'Role');
    }

    /**
     * Per-hero, per-map and per-role breakdowns, all one internal controller
     * distinguished by two parameters. `single` narrows to one subject and wants
     * it named — `hero`, `map` or `role` — where `all` returns the lot.
     *
     * The public parameter is named after the resource; only `map` differs from
     * what the internal controller calls it.
     */
    private function breakdown(Request $request, string $page, string $type): Response
    {
        if ($type === 'single') {
            $internal = self::SUBJECTS[$page];

            // Without it the internal controller reads a property off a null
            // lookup. A missing subject is a bad request, not a 500.
            if (! $request->filled($page) && ! $request->filled($internal)) {
                return $this->error(
                    'missing_'.$page,
                    'This endpoint needs a '.$page.' to narrow to.',
                    422
                );
            }

            if ($page !== $internal && $request->filled($page)) {
                $request->merge([$internal => $request->input($page)]);
            }
        }

        return $this->delegate($request, PlayerHeroesMapsRolesController::class, 'getData', [
            'page' => $page,
            'type' => $type,
            // Not optional in practice: the internal controller resolves this to
            // null when absent and then calls in_array() on it. `sl` is what the
            // site sends for a visitor who is not logged in.
            'game_type' => self::DEFAULT_GAME_TYPE,
        ], ['game_type' => 'array']);
    }

    /**
     * Ratings for the account, per hero, or per role — same controller, one flag.
     *
     * `Hero` and `Role` carry `required_if` rules internally, and that controller
     * answers a validation failure as a 200 with a `status` field. Catching the
     * missing subject here means a caller gets a 422 they can act on instead.
     */
    private function ratings(Request $request, string $type): Response
    {
        $subject = strtolower($type);

        if ($type !== 'Player' && ! $request->filled($subject)) {
            return $this->error(
                'missing_'.$subject,
                'This endpoint needs a '.$subject.' to report ratings for.',
                422
            );
        }

        return $this->delegate($request, PlayerMMRController::class, 'getData', [
            'type' => $type,
            // Scalar here, unlike the breakdown endpoints: this controller looks
            // the short name up with `where`, not `whereIn`. Absent, it resolves
            // to null and the query then matches no rows at all.
            'game_type' => self::DEFAULT_GAME_TYPE,
        ], ['hero' => 'id']);
    }

    public function talentBuild(Request $request): Response
    {
        // Required internally, and a miss there answers 200 with an error body
        // rather than a status a caller can branch on.
        if (! $request->filled('hero')) {
            return $this->error('missing_hero', 'This endpoint needs a hero to report builds for.', 422);
        }

        return $this->delegate($request, PlayerTalentsController::class, 'getPlayerTalentData', [
            'game_type' => self::DEFAULT_GAME_TYPE,
        ], ['game_type' => 'array']);
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
