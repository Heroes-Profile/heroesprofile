<?php

namespace App\Http\Controllers;

use App\Models\GameType;
use App\Models\Map;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Services\AnalyticsQueryService;
use App\Services\AnalyticsReplayService;
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
    public function __construct(
        GlobalDataService $globalDataService,
        private AnalyticsService $analytics,
        private AnalyticsQueryService $queries,
        private AnalyticsReplayService $replays,
    ) {
        parent::__construct($globalDataService);
    }

    public function show(Request $request): View
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

        return response()->json($this->queries->tab($tab, (int) $user->blizz_id, (int) $user->region, $filters));
    }

    // The replay viewer: any season game for any Battle.net login (no allowlist), processed on demand.
    // The page opens straight away and the viewer waits for the game to be ready, starting its
    // processing if nobody has yet
    public function replay(Request $request, int $replayID): View
    {
        $this->loggedInUser();
        abort_unless($this->replays->isSeasonGame($replayID), 404);

        return view('Analytics.analyticsReplay')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'replayId' => $replayID,
            'urlparameters' => $request->query(),
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
