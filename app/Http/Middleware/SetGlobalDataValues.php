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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $globalDataService = new GlobalDataService();
        $globalDataService->calculateMaxReplayNumber();
        $globalDataService->getLatestPatch();
        $globalDataService->getLatestGameDate();
        $globalDataService->getHeroes();
        //$globalDataService->getFilterData();
        $globalDataService->getDefaultTimeframe();
        $globalDataService->getDefaultBuildType();

        return $next($request);
    }
}
