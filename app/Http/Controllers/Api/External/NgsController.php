<?php

namespace App\Http\Controllers\Api\External;

use App\Auth\ApiKeyGuard;
use App\Http\Controllers\Api\External\Concerns\TranslatesInternalFailures;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Esports\EsportsController;
use App\Http\Controllers\Esports\NGS\NGSController as SiteNgsController;
use App\Http\Controllers\Esports\NGS\NGSSingleDivisionController;
use App\Http\Controllers\SingleMatchController;
use App\Models\NGS\Battletag as NgsBattletag;
use App\Models\NGS\NGSTeam;
use App\Rules\NgsReplayUrlValidation;
use App\Services\Api\NgsReplayIngestService;
use App\Support\GameLength;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * NGS reads, one per section of the site's NGS pages, metered like any other
 * read. Ingestion is restricted to accounts granted NGS upload access and
 * carries no weekly quota — see RequireNgsAccess.
 */
class NgsController extends Controller
{
    use TranslatesInternalFailures;

    public function standings(Request $request): Response
    {
        return $this->delegate($request, SiteNgsController::class, 'getStandingData', ['season', 'division'], defaultSeason: true);
    }

    public function divisions(Request $request): Response
    {
        return $this->delegate($request, SiteNgsController::class, 'getDivisionData', ['season'], defaultSeason: true);
    }

    public function teams(Request $request): Response
    {
        return $this->delegate($request, SiteNgsController::class, 'getTeamsData', ['season', 'division'], defaultSeason: true);
    }

    /** `battletag` is a full battletag or the part before the `#`. */
    public function playerSearch(Request $request): Response
    {
        if (! $request->filled('battletag')) {
            return $this->error('missing_battletag', 'This endpoint needs a battletag to search for.');
        }

        return $this->delegate($request, SiteNgsController::class, 'playerSearch', [], [
            'userinput' => $request->input('battletag'),
        ]);
    }

    public function matches(Request $request): Response
    {
        return $this->delegate($request, EsportsController::class, 'getRecentMatchData', ['season', 'division', 'hero'], [
            'pagination_page' => $this->page($request),
        ], defaultSeason: true);
    }

    public function heroStats(Request $request): Response
    {
        return $this->delegate($request, EsportsController::class, 'getOverallHeroStats', ['season', 'division'], defaultSeason: true);
    }

    public function heroTalentStats(Request $request): Response
    {
        if (! $request->filled('hero')) {
            return $this->error('missing_hero', 'This endpoint needs a hero to report talents for.');
        }

        return $this->delegate($request, EsportsController::class, 'getOverallTalentStats', ['season', 'division', 'hero'], defaultSeason: true);
    }

    public function division(Request $request): Response
    {
        if (! $request->filled('division')) {
            return $this->error('missing_division', 'This endpoint needs a division.');
        }

        return $this->delegate($request, NGSSingleDivisionController::class, 'getSingleDivisionData', ['season', 'division'], defaultSeason: true);
    }

    public function divisionMatches(Request $request): Response
    {
        if (! $request->filled('division')) {
            return $this->error('missing_division', 'This endpoint needs a division.');
        }

        return $this->delegate($request, NGSSingleDivisionController::class, 'getSingleDivisionMatchHistory', ['season', 'division'], defaultSeason: true);
    }

    /*
    | The shared esports pages. Season is optional here and absence means every
    | season, as on the site.
    */

    public function team(Request $request): Response
    {
        if (! $request->filled('team')) {
            return $this->error('missing_team', 'This endpoint needs a team name.');
        }

        return $this->delegate($request, EsportsController::class, 'getData', ['team', 'season', 'division']);
    }

    public function teamMatches(Request $request): Response
    {
        if (! $request->filled('team')) {
            return $this->error('missing_team', 'This endpoint needs a team name.');
        }

        return $this->delegate($request, EsportsController::class, 'getTeamMatchHistoryData', ['team', 'season', 'division'], [
            'pagination_page' => $this->page($request),
        ]);
    }

    public function player(Request $request): Response
    {
        return $this->delegatePlayer($request);
    }

    public function playerHero(Request $request): Response
    {
        if (! $request->filled('hero')) {
            return $this->error('missing_hero', 'This endpoint needs a hero.');
        }

        return $this->delegatePlayer($request, ['hero']);
    }

    public function playerMap(Request $request): Response
    {
        if (! $request->filled('game_map')) {
            return $this->error('missing_game_map', 'This endpoint needs a game_map.');
        }

        return $this->delegatePlayer($request, ['game_map']);
    }

    /**
     * No season filter: the site controller reads `season` as a ranked season and
     * filters by its dates, which means nothing for NGS. The page never sends one.
     */
    public function playerMatches(Request $request): Response
    {
        return $this->delegatePlayer($request, [], ['pagination_page' => $this->page($request)], 'getDataSinglePlayerMatchHistory', seasonal: false);
    }

    public function replay(Request $request, int $replayID): Response
    {
        $result = $this->delegate($request, SingleMatchController::class, 'getData', [], ['replayID' => $replayID]);

        if ($result->getStatusCode() === 404) {
            return $this->error('replay_not_found', 'No NGS match found for that id.', 404);
        }

        // Seconds, as every other endpoint reports length.
        return $result instanceof JsonResponse && $result->isOk()
            ? response()->json(GameLength::inPayload($result->getData(true)))
            : $result;
    }

    /**
     * Shared with ValidateNgsUpload, which runs these before the fixtures gate.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function uploadRules(): array
    {
        return [
            // Lengths match `heroesprofile_logs.ngs_replays_sent`, the narrowest place
            // each value lands. That connection runs in strict mode, so anything
            // longer throws on insert rather than truncating — better a 422 here.
            'replay_url' => ['required', 'string', 'max:200', new NgsReplayUrlValidation],
            'mode' => ['required', 'string', 'in:prod,dev'],
            'season' => ['required', 'integer', 'min:1'],
            'round' => ['required', 'string', 'max:45'],
            'game' => ['required', 'string', 'max:45'],
            'team_one_name' => ['required', 'string', 'max:200'],
            'team_two_name' => ['required', 'string', 'max:200'],
            'team_one_player' => ['required', 'string', 'max:45'],
            'team_two_player' => ['required', 'string', 'max:45'],
            // Required rather than defaulted: the old handler let all four fall
            // through as null and then rejected the upload further down.
            'team_one_map_ban_1' => ['required', 'string', 'max:200'],
            'team_one_map_ban_2' => ['required', 'string', 'max:200'],
            'team_two_map_ban_1' => ['required', 'string', 'max:200'],
            'team_two_map_ban_2' => ['required', 'string', 'max:200'],
            'team_one_image_url' => ['sometimes', 'nullable', 'string', 'max:200'],
            'team_two_image_url' => ['sometimes', 'nullable', 'string', 'max:200'],
            // All three defaulted to 'NGS' on the old site.
            'tournament' => ['sometimes', 'string', 'max:45'],
            'team_one_division' => ['sometimes', 'string', 'max:45'],
            'team_two_division' => ['sometimes', 'string', 'max:45'],
        ];
    }

    /**
     * Ingests one NGS custom game.
     *
     * Parses synchronously and answers with the match and player links, because the
     * NGS tooling posts a game and expects somewhere to send people. The old handler
     * authenticated by interpolating the caller's token into SQL; that is now
     * `api.ngs:upload`, and `api_token` is no longer read from the request at all.
     */
    public function uploadGames(Request $request, NgsReplayIngestService $ingest): JsonResponse
    {
        $validated = $request->validate(self::uploadRules());

        $validated['tournament'] ??= 'NGS';
        $validated['team_one_division'] ??= 'NGS';
        $validated['team_two_division'] ??= 'NGS';
        $validated['api_key_reference'] = $this->keyReference($request);

        try {
            $payload = $ingest->ingest($validated, $this->connectionFor($validated['mode']));
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => ['code' => 'ngs_upload_failed', 'message' => $e->getMessage()],
            ], 422);
        }

        return response()->json($payload);
    }

    /**
     * Removes a game and its players, talents, scores, bans and draft. Admin only —
     * see RequireApiAdmin.
     */
    public function deleteGames(Request $request, NgsReplayIngestService $ingest, int $replayID): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:prod,dev'],
        ]);

        // There is no fixture for a delete — nothing sensible to hand back — so the
        // gate is explicit. An admin with test mode on would otherwise remove live
        // rows, which is the one thing test mode exists to prevent.
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context?->servesFixtures()) {
            return response()->json([
                'error' => [
                    'code' => 'test_mode',
                    'message' => 'This account is receiving test data and cannot delete NGS games.',
                ],
            ], 403);
        }

        $ingest->delete($replayID, $this->connectionFor($validated['mode']));

        return response()->json(['deleted' => $replayID]);
    }

    /**
     * A player by `blizz_id`, or by `battletag` alone — resolved against the NGS
     * battletags table. `blizz_id` is what the site filters on; without it the
     * query covers every player.
     *
     * @param  array<int, string>  $keys
     * @param  array<string, mixed>  $set
     */
    private function delegatePlayer(Request $request, array $keys = [], array $set = [], string $method = 'getData', bool $seasonal = true): Response
    {
        if ($request->filled('blizz_id')) {
            if (! ctype_digit((string) $request->input('blizz_id'))) {
                return $this->error('invalid_blizz_id', 'blizz_id must be a number.');
            }

            $blizzId = (string) $request->input('blizz_id');
        } elseif ($request->filled('battletag')) {
            $matches = $this->ngsBlizzIds((string) $request->input('battletag'));

            if ($matches === []) {
                return $this->error('player_not_found', 'No NGS player found for that battletag.', 404);
            }

            if (count($matches) > 1) {
                return $this->error('ambiguous_player', 'More than one NGS player matches that battletag. Send the full battletag, or a blizz_id from `ngs/players/search`.');
            }

            $blizzId = (string) $matches[0];
        } else {
            return $this->error('missing_player', 'This endpoint needs a battletag or a blizz_id.');
        }

        $keys = array_merge(['battletag'], $seasonal ? ['season', 'division'] : [], $keys);

        // The site rules want `blizz_id` as a string, which a query string always
        // is but an internal caller's integer is not.
        $set['blizz_id'] = $blizzId;

        return $this->delegate($request, EsportsController::class, $method, $keys, $set);
    }

    /**
     * Distinct blizz_ids for a battletag. The part before the `#` matches every
     * discriminator, as the site's search does.
     *
     * @return array<int, int|string>
     */
    private function ngsBlizzIds(string $battletag): array
    {
        $battletag = str_replace(' ', '', $battletag);

        return NgsBattletag::query()
            ->when(
                str_contains($battletag, '#'),
                fn ($query) => $query->where('battletag', $battletag),
                fn ($query) => $query->where('battletag', 'LIKE', addcslashes($battletag, '%_\\').'#%')
            )
            ->distinct()
            ->pluck('blizz_id')
            ->all();
    }

    /**
     * Builds a fresh request from the listed caller parameters only. The site
     * controllers are shared across esports and read `team`, `blizz_id`, `hero`,
     * `tournament` and more from whatever arrives, so a stray parameter would
     * reshape the query rather than be ignored.
     *
     * The site's NGS pages always send a season, and several of their controllers
     * filter on it unconditionally — a missing one matches nothing rather than
     * everything. Those default it to the latest.
     *
     * @param  array<int, string>  $keys
     * @param  array<string, mixed>  $set
     */
    private function delegate(Request $request, string $controller, string $method, array $keys, array $set = [], bool $defaultSeason = false): Response
    {
        $input = array_filter($request->only($keys), fn ($value) => $value !== null && $value !== '');

        if ($defaultSeason && ! isset($input['season'])) {
            $input['season'] = NGSTeam::max('season');
        }

        $internal = Request::create($request->path(), 'GET', array_merge($input, $set, ['esport' => 'NGS']));

        $result = app()->call([app($controller), $method], ['request' => $internal]);

        if ($failure = $this->internalFailure($result)) {
            return $failure;
        }

        // The site's paginator links to its own page parameter with none of the
        // caller's filters.
        if ($result instanceof LengthAwarePaginator) {
            $result->setPageName('pagination_page');
            $result->withPath($request->url())->appends(Arr::except($request->query(), ['api_token', 'pagination_page']));
        }

        return $result instanceof Response ? $result : response()->json($result);
    }

    private function page(Request $request): int
    {
        return max(1, (int) $request->input('pagination_page', 1));
    }

    private function error(string $code, string $message, int $status = 422): JsonResponse
    {
        return response()->json([
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }

    /** `dev` writes to the scratch copy of the NGS schema, as it did on the old site. */
    private function connectionFor(string $mode): string
    {
        return $mode === 'prod' ? 'heroesprofile_ngs' : 'heroesprofile_ngs_dev';
    }

    /**
     * Which key uploaded, for the log. Keys are stored hashed, so the plaintext the
     * old column held is not available to record.
     */
    private function keyReference(Request $request): string
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        return $context === null ? 'unknown' : 'key:'.$context->keyId;
    }
}
