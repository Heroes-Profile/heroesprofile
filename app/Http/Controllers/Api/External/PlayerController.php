<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Api\External\Concerns\TranslatesInternalFailures;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerAwardsController;
use App\Http\Controllers\Player\PlayerController as InternalPlayerController;
use App\Http\Controllers\Player\PlayerHeroesMapsRolesController;
use App\Http\Controllers\Player\PlayerMatchHistory;
use App\Http\Controllers\Player\PlayerMatchupsController;
use App\Http\Controllers\Player\PlayerMMRController;
use App\Http\Controllers\Player\PlayerTalentsController;
use App\Services\PlayerMmrService;
use App\Support\ApiParameters;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
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
    use TranslatesInternalFailures;

    /**
     * What player pages show a visitor who is not logged in — see
     * `GlobalDataService::getPlayerGameTypeDefault()`. The public API is always
     * anonymous, so this is always the answer.
     */
    private const DEFAULT_GAME_TYPES = ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'];

    /** Rating history is one game type at a time, so it keeps a single default. */
    private const MMR_DEFAULT_GAME_TYPE = 'sl';

    /** Suggested seconds between polls, the same as the global stats endpoints. */
    private const POLL_INTERVAL = 10;

    /** A day of changes fits comfortably; the ceiling bounds a first sync. */
    private const PRIVACY_DEFAULT_LIMIT = 1000;

    private const PRIVACY_MAX_LIMIT = 5000;

    /** Public parameter name => what the internal controller calls it. */
    private const SUBJECTS = [
        'hero' => 'hero',
        'map' => 'game_map',
        'role' => 'role',
    ];

    public function profile(Request $request): Response
    {
        // No game_type default: the controller reads its absence as every type.
        return $this->delegate($request, InternalPlayerController::class, 'getPlayerData', [], [
            'game_type' => 'array',
            'season' => 'array',
        ]);
    }

    public function matches(Request $request): Response
    {
        // Built inside a job, which cannot see this request, so the page links are
        // handed over rather than fixed up below.
        $request->merge(['links' => [
            'path' => $request->url(),
            'query' => Arr::except($request->query(), ['api_token', 'pagination_page', 'links']),
        ]]);

        return $this->delegate($request, PlayerMatchHistory::class, 'getData', [
            'pagination_page' => 1,
            'game_type' => self::DEFAULT_GAME_TYPES,
        ], ['hero' => 'id', 'game_type' => 'array', 'game_map' => 'array', 'season' => 'array']);
    }

    /**
     * How often this player earns each end of match award, plus the latest five.
     */
    public function awards(Request $request): Response
    {
        return $this->delegate($request, PlayerAwardsController::class, 'getData', [
            'game_type' => self::DEFAULT_GAME_TYPES,
        ], ['hero' => 'id', 'game_type' => 'array', 'game_map' => 'array', 'season' => 'array']);
    }

    /**
     * Every game where this player earned one award, newest first, 100 a page.
     */
    public function awardGames(Request $request): Response
    {
        if (! $request->filled('award_id')) {
            return $this->error('missing_award_id', 'This endpoint needs an award_id. `players/awards` lists them.', 422);
        }

        return $this->delegate($request, PlayerAwardsController::class, 'getAwardGames', [
            'pagination_page' => 1,
            'game_type' => self::DEFAULT_GAME_TYPES,
        ], ['hero' => 'id', 'game_type' => 'array', 'game_map' => 'array', 'season' => 'array']);
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
            // Reaches Map::whereIn('name', …).
            'game_map' => 'array',
            'season' => 'array',
        ], ['tabledata' => 'matchups']);
    }

    /**
     * Team-mates and opponents this player sees repeatedly.
     *
     * One call answers one side. `type` is not defaulted: friend and enemy are
     * different questions, and picking one returns data nobody asked for.
     */
    public function friendFoe(Request $request): Response
    {
        if (! $request->filled('type')) {
            return $this->error(
                'missing_type',
                'This endpoint needs a type: `friend` for team-mates, `enemy` for opponents.',
                422
            );
        }

        return $this->delegate($request, FriendFoeController::class, 'getFriendFoeData', [
            // Required here, unlike `players/matchups`, which reads its absence as
            // every game type. This one puts it straight into a whereIn().
            'game_type' => self::DEFAULT_GAME_TYPES,
        ], [
            'hero' => 'id',
            'game_type' => 'array',
            'game_map' => 'array',
            'season' => 'array',
        ]);
    }

    /**
     * Current rating per game type — what the old API's `/Player/MMR` returned.
     *
     * The site has no page for this, so there is nothing to delegate to; the query
     * lives in PlayerMmrService. The rating *history* these URLs used to return is
     * now under `/players/mmr/history`.
     */
    public function mmr(Request $request): Response
    {
        return $this->currentRating($request, null);
    }

    public function heroMmr(Request $request): Response
    {
        return $this->currentRating($request, 'hero');
    }

    public function roleMmr(Request $request): Response
    {
        return $this->currentRating($request, 'role');
    }

    public function mmrHistory(Request $request): Response
    {
        return $this->ratings($request, 'Player');
    }

    public function heroMmrHistory(Request $request): Response
    {
        return $this->ratings($request, 'Hero');
    }

    public function roleMmrHistory(Request $request): Response
    {
        return $this->ratings($request, 'Role');
    }

    /**
     * `$subjectParam` names the parameter holding what is being rated — `hero` or
     * `role` — or null for the account overall.
     */
    private function currentRating(Request $request, ?string $subjectParam): Response
    {
        if ($subjectParam !== null && ! $request->filled($subjectParam)) {
            return $this->error(
                'missing_'.$subjectParam,
                'This endpoint needs a '.$subjectParam.' to report a rating for.',
                422
            );
        }

        if ($request->filled('region')) {
            [$ids, $unknown] = ApiParameters::regionIds($request->input('region'));

            if ($unknown !== []) {
                return $this->error('unknown_region', 'Not a recognised region: '.implode(', ', $unknown).'. Use NA, EU, KR or CN.', 422);
            }

            $request->merge(['region' => $ids[0] ?? null]);
        }

        $validated = $request->validate([
            'battletag' => ['required', 'string'],
            'region' => ['required', 'integer', 'in:1,2,3,5'],
        ]);

        $gameTypes = [];

        if ($request->filled('game_type')) {
            [$gameTypes, $unknown] = ApiParameters::gameTypes($request->input('game_type'));

            if ($unknown !== []) {
                return $this->error('unknown_game_type', 'Not a recognised game type: '.implode(', ', $unknown).'.', 422);
            }
        }

        $blizzId = $this->globalDataService->getBlizzIDGivenFullBattletag(
            $validated['battletag'],
            $validated['region']
        );

        if ($blizzId === null) {
            return $this->error('player_not_found', 'No player found for that battletag and region.', 404);
        }

        if ($this->globalDataService->isRestrictedAccount($blizzId, $validated['region'])) {
            return $this->error('player_unavailable', 'That player has made their profile private.', 403);
        }

        // An unknown name would otherwise read as a player with no rating.
        if ($subjectParam !== null && $this->globalDataService->getMMRTypeValue($request->input($subjectParam)) === null) {
            return $this->error('unknown_'.$subjectParam, 'Not a recognised '.$subjectParam.': '.$request->input($subjectParam).'.', 422);
        }

        $ratings = app(PlayerMmrService::class)->summary(
            (int) $blizzId,
            (int) $validated['region'],
            $gameTypes,
            $subjectParam === null ? null : $request->input($subjectParam),
            $request->boolean('extra_mmr_info')
        );

        return response()->json([
            'battletag' => $validated['battletag'],
            'region' => (int) $validated['region'],
            'ratings' => $ratings,
        ]);
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

            // The site offers a hero filter only on the "all" pages. Here the
            // controller would ignore it for the matches and swap the rating shown.
            if ($page !== 'hero' && $request->has('hero')) {
                return $this->error('unsupported_parameter', '`hero` is not accepted here. Use the "all" endpoint to filter by hero.', 422);
            }

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

        $expects = ['game_type' => 'array', 'season' => 'array'];

        // A list everywhere except the single map page, where `game_map` is the map
        // itself and read with Map::where().
        if (! ($type === 'single' && $page === 'map')) {
            $expects['game_map'] = 'array';
        }

        return $this->delegate($request, PlayerHeroesMapsRolesController::class, 'getData', [
            'page' => $page,
            'type' => $type,
            // Not optional in practice: the internal controller resolves this to
            // null when absent and then calls in_array() on it.
            'game_type' => self::DEFAULT_GAME_TYPES,
        ], $expects);
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

        // History is one game type at a time; the controller would quietly use the
        // first of several.
        if ($request->filled('game_type') && count(ApiParameters::gameTypes($request->input('game_type'))[0]) > 1) {
            return $this->error('single_game_type_only', 'This endpoint takes one game type.', 422);
        }

        return $this->delegate($request, PlayerMMRController::class, 'getData', [
            'type' => $type,
            // Scalar here, unlike the breakdown endpoints: this controller looks
            // the short name up with `where`, not `whereIn`. Absent, it resolves
            // to null and the query then matches no rows at all.
            'game_type' => self::MMR_DEFAULT_GAME_TYPE,
        ], ['hero' => 'id', 'season' => 'array'], [
            // `tableData` is the per-match rating history; `leagueData` is the tier
            // bands it sits within. Both named after the components that render
            // them on the site rather than after what they hold.
            'tableData' => 'history',
            'leagueData' => 'league_tiers',
        ], ['history' => [], 'league_tiers' => []]);
    }

    public function talentBuild(Request $request): Response
    {
        // Required internally, and a miss there answers 200 with an error body
        // rather than a status a caller can branch on.
        if (! $request->filled('hero')) {
            return $this->error('missing_hero', 'This endpoint needs a hero to report builds for.', 422);
        }

        // `fromdate` was this endpoint's only date filter before start_date/end_date.
        // Kept so existing callers still get the range they asked for.
        if ($request->filled('fromdate') && ! $request->filled('start_date')) {
            $request->merge(['start_date' => $request->input('fromdate')]);
        }

        return $this->delegate($request, PlayerTalentsController::class, 'getPlayerTalentData', [
            'game_type' => self::DEFAULT_GAME_TYPES,
        ], ['game_type' => 'array', 'game_map' => 'array', 'season' => 'array']);
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
        array $expects = [],
        array $rename = [],
        ?array $whenEmpty = null
    ): Response {
        // Before any merge: on a GET, merge() writes into the query bag.
        $callerQuery = Arr::except($request->query(), ['api_token', 'pagination_page']);

        // Names in, ids out — the mirror of the global endpoints, which want region
        // names. `NA` and `1` both work here, and `Storm League` alongside `sl`.
        // Runs before validation, which is what enforces the id form.
        if ($request->filled('region')) {
            [$ids, $unknown] = ApiParameters::regionIds($request->input('region'));

            if ($unknown !== []) {
                return $this->error('unknown_region', 'Not a recognised region: '.implode(', ', $unknown).'. Use NA, EU, KR or CN.', 422);
            }

            $request->merge(['region' => $ids[0] ?? null]);
        }

        if ($request->filled('game_type')) {
            [$codes, $unknown] = ApiParameters::gameTypes($request->input('game_type'));

            if ($unknown !== []) {
                return $this->error('unknown_game_type', 'Not a recognised game type: '.implode(', ', $unknown).'. Accepted: '.implode(', ', ApiParameters::GAME_TYPES).'.', 422);
            }

            $input = $request->input('game_type');
            $request->merge([
                'game_type' => is_string($input) && ! str_contains($input, ',')
                    ? ($codes[0] ?? $input)
                    : $codes,
            ]);
        }

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

        // `game_type=` arrives as null; treat it as not sent.
        foreach ($defaults as $key => $value) {
            if (! $request->filled($key)) {
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

        // Any parameter declared `array` is split, not just game_type. Which ones
        // need it differs per endpoint and cannot be defaulted: `matches`,
        // `matchups` and `talentBuild` reach `Map::whereIn('name', …)` and need an
        // array, while `friendFoe` reaches `Map::where('name', …)` and needs a
        // scalar. Getting it wrong either way fails inside the query builder.
        foreach ($expects as $key => $kind) {
            if ($kind === 'array' && $request->filled($key) && is_string($request->input($key))) {
                $request->merge([$key => explode(',', (string) $request->input($key))]);
            }
        }

        $result = app()->call([app($controller), $method], ['request' => $request]);

        if ($failure = $this->internalFailure($result)) {
            return $failure;
        }

        // The site's paginator links to its own page parameter with none of the
        // caller's filters, so following one silently dropped them.
        if ($result instanceof LengthAwarePaginator) {
            $result->setPageName('pagination_page');
            $result->withPath($request->url())->appends($callerQuery);
        }

        if ($result === null && $whenEmpty !== null) {
            $result = $whenEmpty;
        }

        // Some of these return an array, others a response object. Wrapping a
        // response in response()->json() serialises the object itself, so the
        // caller receives {"headers":…,"original":…} instead of the payload.
        if ($result instanceof Response) {
            // A cold breakdown, friendfoe or awards query becomes a job. Point the
            // caller at it the way the global stats endpoints do.
            if ($result instanceof JsonResponse && $result->getStatusCode() === 202) {
                $jobId = $result->getData(true)['job_id'] ?? null;

                if ($jobId !== null) {
                    $result->header('Retry-After', self::POLL_INTERVAL)
                        ->header('Location', route('api.external.jobs', ['jobId' => $jobId]));
                }
            }

            return $result;
        }

        // These controllers were written for the site's own pages and name their
        // top-level keys after the components that render them — `tableData` is a
        // table because a table displays it. That tells an API caller nothing, so
        // each endpoint renames what it returns to what the data actually is.
        foreach ($rename as $from => $to) {
            if (is_array($result) && array_key_exists($from, $result)) {
                $result = self::renameKey($result, $from, $to);
            }
        }

        return response()->json($result);
    }

    /**
     * Replaces one key, in place. Rebuilding the array rather than unset-and-append
     * so the renamed key keeps its position — callers reading a response by eye
     * should not find it moved to the bottom.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function renameKey(array $data, string $from, string $to): array
    {
        $renamed = [];

        foreach ($data as $key => $value) {
            $renamed[$key === $from ? $to : $key] = $value;
        }

        return $renamed;
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }

    /**
     * Privacy changes since a timestamp, so a caller can purge players who have
     * gone private from data it cached while they were public.
     *
     * The rest of the API refuses a private player at the point of the call; this
     * is the only way to reach a copy already sitting in someone else's database.
     * The terms of service require polling it daily.
     *
     * `battletag` and `region` rather than blizz_id because that is the pair every
     * other endpoint answers with — a caller has never seen a blizz_id to match on.
     */
    public function privacyChanges(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => ['sometimes', 'date'],
            'after_id' => ['sometimes', 'integer', 'min:0'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.self::PRIVACY_MAX_LIMIT],
            'mode' => ['sometimes', 'in:json,csv'],
        ]);

        $limit = (int) ($validated['limit'] ?? self::PRIVACY_DEFAULT_LIMIT);

        // Omitted means everything, which is the first-sync case. The result is a
        // snapshot rather than an event log, so reaching back to the beginning
        // costs one row per account rather than one per change.
        $since = isset($validated['since']) ? Carbon::parse($validated['since']) : null;

        $changes = $this->globalDataService->getPrivacyChanges(
            $since,
            isset($validated['after_id']) ? (int) $validated['after_id'] : null,
            $limit
        );

        $last = $changes->last();

        // The cursor is the last row handed out, not "now" — a change written while
        // this response was being built would otherwise be skipped forever. When
        // nothing came back the caller keeps the cursor it already had.
        return response()->json([
            'changes' => $changes->map(fn ($change) => Arr::except($change, 'id'))->values(),
            'next_since' => $last['changed_at'] ?? $since?->toIso8601String(),
            'next_after_id' => $last['id'] ?? ($validated['after_id'] ?? null),
            // A full page means ask again. It is not a promise there is more —
            // the last page of an exact multiple comes back empty.
            'has_more' => $changes->count() === $limit,
        ]);
    }
}
