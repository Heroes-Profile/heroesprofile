<?php

namespace App\Http\Middleware;

use App\Services\GlobalDataService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetGlobalDataValues
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkipGlobalDataWarmup($request)) {
            return $next($request);
        }

        $globalDataService = new GlobalDataService;
        $globalDataService->calculateMaxReplayNumber();
        $globalDataService->getLatestPatch();
        $globalDataService->getLatestGameDate();
        $globalDataService->getHeaderAlert();

        return $next($request);
    }

    private function shouldSkipGlobalDataWarmup(Request $request): bool
    {
        return $request->is(
            'api/v1/global/status/*',
            'api/v1/internal/global/process',
        );
    }
}
