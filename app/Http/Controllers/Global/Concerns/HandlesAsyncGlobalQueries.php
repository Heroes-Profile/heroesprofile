<?php

namespace App\Http\Controllers\Global\Concerns;

use App\Services\GlobalQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

trait HandlesAsyncGlobalQueries
{
    protected function asyncGlobalResponse(
        Request $request,
        string $cacheKey,
        array $gameVersion,
        string $executeMethod
    ): JsonResponse|array {
        $cacheTtlSeconds = $this->globalDataService->calculateCacheTimeInSeconds($gameVersion);
        $bypassCache = $this->globalDataService->shouldBypassGlobalCache();

        if (! $this->globalDataService->isGlobalAsyncEnabled()) {
            if ($bypassCache || config('app.env') !== 'production') {
                Cache::store('database')->forget($cacheKey);
            }

            $data = $bypassCache
                ? app(static::class)->{$executeMethod}($request)
                : Cache::store('database')->remember($cacheKey, $cacheTtlSeconds, function () use ($request, $executeMethod) {
                    return app(static::class)->{$executeMethod}($request);
                });

            $response = response()->json($data)
                ->header('X-Global-Async-Mode', 'sync');

            if ($bypassCache) {
                $response->header('X-Global-Cache-Bypass', 'true');
            }

            return $response;
        }

        return app(GlobalQueryService::class)->handle(
            $cacheKey,
            static::class,
            $executeMethod,
            $request->all(),
            $cacheTtlSeconds
        );
    }
}
