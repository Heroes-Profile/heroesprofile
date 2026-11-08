<?php

namespace App\Http\Controllers;

use App\Models\GameType;
use App\Models\Map;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Services\AnalyticsPerformanceService;
use App\Services\AnalyticsQueryService;
use App\Services\AnalyticsReplayService;
use App\Services\AnalyticsSampleService;
use App\Services\AnalyticsService;
use App\Services\GlobalDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Analytics for the logged-in Battle.net account's own season games, for current Patreon
 * supporters (and the testing allowlist). Everyone else gets the info page, and 404s from the
 * data endpoints. The replay viewer below is open to any Battle.net login.
 */
class AnalyticsController extends Controller
{
    // Each section's path under /Analytics, and the page's tab for it; /Analytics alone is the summary
    public const SECTIONS = [
        'Deaths' => 'deaths',
        'Map' => 'map',
        'Positioning' => 'positioning',
        'Timelines' => 'timelines',
        'Activity' => 'activity',
        'Sessions' => 'sessions',
        'Coaching' => 'coaching',
        'Stacks' => 'stacks',
        'Match/History' => 'games',
    ];

    public const SECTION_PATTERN = 'Deaths|Map|Positioning|Timelines|Activity|Sessions|Coaching|Stacks|Match/History';

    // One match's sections: those that work for a single match
    public const MATCH_SECTION_PATTERN = 'Deaths|Map|Positioning|Timelines|Activity|Coaching';

    public function __construct(
        GlobalDataService $globalDataService,
        private AnalyticsService $analytics,
        private AnalyticsQueryService $queries,
        private AnalyticsReplayService $replays,
        private AnalyticsSampleService $sample,
        private AnalyticsPerformanceService $performance,
    ) {
        parent::__construct($globalDataService);
    }

    public function show(Request $request, ?string $section = null)
    {
        // Old ?tab= / ?game= links
        $old = array_flip(self::SECTIONS);
        if ($request->query('game')) {
            return redirect('/Analytics/Match/Single/'.(int) $request->query('game').(isset($old[$request->query('tab')]) && $request->query('tab') !== 'games' ? '/'.$old[$request->query('tab')] : '').$this->filterQuery($request), 301);
        }
        if ($request->query('tab')) {
            return redirect('/Analytics'.(isset($old[$request->query('tab')]) ? '/'.$old[$request->query('tab')] : '').$this->filterQuery($request), 301);
        }

        return $this->page($request, $section === null ? 'summary' : self::SECTIONS[$section], null);
    }

    // One match against the rest: the same page, for that match alone
    public function showMatch(Request $request, int $replayID, ?string $section = null)
    {
        $user = $this->loggedInUser();
        // Replay links from before the viewer moved (coaching examples carry ?t=), and anyone without
        // Analytics, go to the replay viewer
        if ($request->has('t') || ! $this->analytics->isAllowed($user)) {
            return redirect('/Analytics/Match/Single/'.$replayID.'/Replay'.($request->getQueryString() ? '?'.$request->getQueryString() : ''));
        }

        return $this->page($request, $section === null ? 'deaths' : self::SECTIONS[$section], $replayID);
    }

    /** The filters of a request as a query string (?game_type=...&game_map=...&hero=...), or empty. */
    private function filterQuery(Request $request): string
    {
        $query = http_build_query(array_filter($request->only(['game_type', 'game_map', 'hero'])));

        return $query === '' ? '' : '?'.$query;
    }

    private function page(Request $request, string $tab, ?int $replayID): View
    {
        $user = Auth::user();

        if (! $user || ! $this->analytics->isAllowed($user)) {
            return view('Analytics.analyticsInfo')->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'loggedIn' => (bool) $user,
                'patreonLinked' => (bool) $user?->patreonAccount,
            ]);
        }

        return view('Analytics.analytics')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'battletag' => explode('#', (string) $user->battletag)[0],
            'status' => $this->analytics->status((int) $user->blizz_id, (int) $user->region),
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
            'maps' => $this->globalDataService->getAllMapsKeyed()->map(fn ($map) => $map->name),
            'urlparameters' => $request->query(),
            'playermatchtablestyle' => $this->globalDataService->getPlayerMatchStyle(),
            'initialTab' => $tab,
            'initialMatch' => $replayID,
        ]);
    }

    public function status(): JsonResponse
    {
        $user = $this->allowedUser();

        return response()->json($this->analytics->status((int) $user->blizz_id, (int) $user->region));
    }

    public function request(): JsonResponse
    {
        $user = $this->allowedUser();

        return response()->json($this->analytics->request((int) $user->blizz_id, (int) $user->region));
    }

    public function data(Request $request, string $tab): JsonResponse
    {
        $user = $this->allowedUser();
        abort_unless($tab === 'games' || $tab === 'ability' || in_array($tab, AnalyticsQueryService::TABS, true), 404);

        // One game: every tab for that game alone, against the same hero, map and mode
        $game = null;
        if ($request->filled('game') && $tab !== 'games') {
            $replayId = (int) $request->validate(['game' => ['integer', 'min:1']])['game'];
            $game = $this->replays->game((int) $user->blizz_id, (int) $user->region, $replayId);
            abort_unless($game, 404);
        }

        // No selection arrives as an empty list; that means "all", not an invalid value
        foreach (['game_type', 'game_map', 'hero'] as $field) {
            if ($request->input($field) === [] || $request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $validated = $request->validate([
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
        ]);

        // The rules accept an array or a comma-separated string
        $list = fn ($value) => is_array($value) ? $value : explode(',', (string) $value);

        $filters = [
            'game_types' => empty($validated['game_type'])
                ? []
                : GameType::whereIn('short_name', $list($validated['game_type']))->pluck('type_id')->map(fn ($id) => (int) $id)->all(),
            'maps' => empty($validated['game_map'])
                ? []
                : Map::whereIn('name', $list($validated['game_map']))->pluck('map_id')->map(fn ($id) => (int) $id)->all(),
            'hero' => (int) ($validated['hero'] ?? 0),
        ];
        if ($game) {
            $filters = ['game_types' => [$game['game_type_id']], 'maps' => [$game['game_map_id']], 'hero' => $game['hero_id'], 'replay' => $game['replay_id']];
        }

        if ($tab === 'ability') {
            $ability = $request->validate([
                'ability_hero' => ['required', 'integer', 'min:1'],
                'ability_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_]+$/'],
            ]);

            return response()->json(['timeline' => $this->queries->abilityTimeline(
                (int) $user->blizz_id, (int) $user->region, $filters, (int) $ability['ability_hero'], $ability['ability_id']
            )]);
        }

        if ($tab === 'games') {
            return response()->json(['games' => $this->replays->games((int) $user->blizz_id, (int) $user->region, $filters)]);
        }

        $data = $this->queries->tab($tab, (int) $user->blizz_id, (int) $user->region, $filters);

        return response()->json($game ? $data + ['game' => $game] : $data);
    }

    // The replay viewer: any season game for any Battle.net login (no allowlist), processed on demand.
    // The page opens straight away and the viewer waits for the game to be ready, starting its
    // processing if nobody has yet
    public function replay(Request $request, int $replayID): View
    {
        $user = $this->loggedInUser();
        abort_unless($this->replays->isSeasonGame($replayID), 404);

        return view('Analytics.analyticsReplay')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'replayId' => $replayID,
            'urlparameters' => $request->query(),
            // Match performance is for Patreon supporters; everyone else sees it greyed out
            'performanceAllowed' => $this->analytics->isAllowed($user),
            'patreonLinked' => (bool) $user->patreonAccount,
        ]);
    }

    public function replayStatus(int $replayID): JsonResponse
    {
        $this->loggedInUser();
        abort_unless($this->replays->isSeasonGame($replayID), 404);

        return response()->json(['ready' => $this->replays->isReady($replayID)]);
    }

    public function replayProcess(int $replayID): JsonResponse
    {
        $this->loggedInUser();
        abort_unless($this->replays->isSeasonGame($replayID), 404);

        return response()->json(['queued' => $this->replays->process($replayID)]);
    }

    public function replayData(int $replayID): JsonResponse
    {
        $user = $this->loggedInUser();
        $replay = $this->replays->replay($replayID, (int) $user->blizz_id);
        abort_unless($replay, 404);
        $replay['win_model'] = $this->queries->winModel();

        return response()->json($replay);
    }

    // How each player in the game played, against the same hero on the same map (Patreon supporters)
    public function replayPerformance(int $replayID): JsonResponse
    {
        $this->allowedUser();
        $performance = $this->performance->forReplay($replayID);
        abort_unless($performance, 404);

        return response()->json($performance);
    }

    // The public sample: the same page and replay viewer, read from the frozen files, for anyone
    public function sample(Request $request): View
    {
        $meta = $this->sample->meta();
        abort_unless($meta, 404);

        // The Map filter offers only the maps the sample has
        $filters = clone $this->globalDataService->getFilterData();
        $filters->game_maps = collect($filters->game_maps)->whereIn('code', $meta['maps'])->values();

        return view('Analytics.analytics')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'battletag' => $meta['battletag'],
            'status' => $meta['status'],
            'filters' => $filters,
            'gametypedefault' => [],
            'maps' => $this->globalDataService->getAllMapsKeyed()->map(fn ($map) => $map->name),
            'urlparameters' => $request->query(),
            'playermatchtablestyle' => $this->globalDataService->getPlayerMatchStyle(),
            'sample' => $meta,
        ]);
    }

    public function sampleData(Request $request, string $tab): JsonResponse
    {
        $meta = $this->sample->meta();
        abort_unless($meta, 404);

        // One sample map at most; anything else isn't in the files
        $maps = $request->input('game_map');
        $maps = is_array($maps) ? $maps : array_filter(explode(',', (string) $maps));
        abort_if(count($maps) > 1, 404);
        $map = $maps[0] ?? null;
        abort_unless($map === null || in_array($map, $meta['maps'], true), 404);

        if ($tab === 'ability') {
            $ability = $request->validate([
                'ability_hero' => ['required', 'integer', 'min:1'],
                'ability_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_]+$/'],
            ]);
            $data = $this->sample->ability((int) $ability['ability_hero'], $ability['ability_id'], $map);
        } else {
            abort_unless($tab === 'games' || in_array($tab, AnalyticsQueryService::TABS, true), 404);
            $data = $this->sample->tab($tab, $map);
        }
        abort_unless($data, 404);

        return response()->json($data);
    }

    public function sampleReplay(Request $request): View
    {
        $meta = $this->sample->meta();
        abort_unless($meta && $meta['replay_id'], 404);

        return view('Analytics.analyticsReplay')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'replayId' => $meta['replay_id'],
            'urlparameters' => $request->query(),
            'sample' => true,
        ]);
    }

    public function sampleReplayData(): JsonResponse
    {
        $replay = $this->sample->replay();
        abort_unless($replay, 404);

        return response()->json($replay);
    }

    // The replay viewer only needs a Battle.net login; the analytics pages also need the allowlist
    private function loggedInUser()
    {
        $user = Auth::user();
        abort_unless($user, 404);

        return $user;
    }

    private function allowedUser()
    {
        $user = Auth::user();

        abort_unless($user && $this->analytics->isAllowed($user), 404);

        return $user;
    }
}
