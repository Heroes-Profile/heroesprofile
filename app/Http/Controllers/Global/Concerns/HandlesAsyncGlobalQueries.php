<?php

namespace App\Http\Controllers\Global\Concerns;

use App\Services\GlobalQueryService;
use App\Support\GlobalCacheKey;
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

    /**
     * The cache key for one global query. See `GlobalCacheKey` for why the
     * parameters are normalised rather than hashed as they arrived.
     *
     * @param  array<int, int|string>  $gameVersionIds
     * @param  array<string, mixed>  $parameters  Usually `$request->all()`.
     */
    protected function globalCacheKey(string $prefix, array $gameVersionIds, array $parameters): string
    {
        return GlobalCacheKey::for($prefix, $gameVersionIds, $parameters);
    }
}
