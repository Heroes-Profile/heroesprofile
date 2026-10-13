<?php

namespace App\Http\Controllers;

use App\Services\DeepAnalyticsService;
use App\Services\GlobalDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Deep analytics for the logged-in Battle.net account's own season games. Only blizz
 * IDs on the deep_analytics allowlist see it until access is opened up; everyone else
 * gets a 404.
 */
class DeepAnalyticsController extends Controller
{
    public function __construct(GlobalDataService $globalDataService, private DeepAnalyticsService $deepAnalytics)
    {
        parent::__construct($globalDataService);
    }

    public function show(): View
    {
        $user = $this->allowedUser();

        return view('DeepAnalytics.deepAnalytics')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'battletag' => $user->battletag,
            'status' => $this->deepAnalytics->status((int) $user->blizz_id, (int) $user->region),
        ]);
    }

    public function status(): JsonResponse
    {
        $user = $this->allowedUser();

        return response()->json($this->deepAnalytics->status((int) $user->blizz_id, (int) $user->region));
    }

    public function request(): JsonResponse
    {
        $user = $this->allowedUser();

        return response()->json($this->deepAnalytics->request((int) $user->blizz_id, (int) $user->region));
    }

    private function allowedUser()
    {
        $user = Auth::user();

        abort_unless($user && $this->deepAnalytics->isAllowed((int) $user->blizz_id), 404);

        return $user;
    }
}
