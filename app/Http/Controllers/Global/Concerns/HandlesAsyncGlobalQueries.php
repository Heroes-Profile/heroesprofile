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
        $bypassCache = $this->globalDataService->shouldBypassGlobalCache();
        $cache = Cache::store('database');

        if ($bypassCache || config('app.env') !== 'production') {
            $cache->forget($cacheKey);
        }

        if (! $bypassCache) {
            $cached = $cache->get($cacheKey);
            if ($cached !== null) {
                return $this->jsonCacheHitResponse($cached);
            }
        }

        $cacheTtlSeconds = $this->globalDataService->calculateCacheTimeInSeconds($gameVersion);

        if (! $this->globalDataService->isGlobalAsyncEnabled()) {

            $data = $bypassCache
                ? app(static::class)->{$executeMethod}($request)
                : $cache->remember($cacheKey, $cacheTtlSeconds, function () use ($request, $executeMethod) {
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

    protected function jsonCacheHitResponse(mixed $data): JsonResponse
    {
        return response()->json($data)
            ->header('X-Global-Cache-Status', 'fresh')
            ->header('X-Global-Async-Mode', 'cache-hit');
    }
}
