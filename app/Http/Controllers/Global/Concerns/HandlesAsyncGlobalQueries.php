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

        if ($request->boolean('group_by_map')) {
            return $this->groupedByMapResponse($request, $cacheKey, $gameVersion, $executeMethod);
        }

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

    /**
     * The same query once per playable map, keyed by map name.
     *
     * Runs no query of its own. Each child is the caller's request with `game_map`
     * pinned to one map and `group_by_map` dropped — which is byte-for-byte the
     * request a caller filtering to that single map would send, so it lands on that
     * exact cache key. A map the site has already drawn for these filters therefore
     * costs this nothing, and the reverse holds too.
     *
     * The old API answered this with one query grouped by `game_map`, guarded by a
     * 600-second time limit and silently disabled on `major` timeframes because a
     * whole patch was too much for it. Neither guard is here: nineteen cached
     * lookups are not one enormous GROUP BY. If a full patch does turn out to hurt,
     * the narrow fix is to refuse `major` in `GlobalStatsController` rather than to
     * quietly answer a different question, which is what the old one did.
     */
    private function groupedByMapResponse(
        Request $request,
        string $cacheKey,
        array $gameVersion,
        string $executeMethod
    ): JsonResponse|array {
        $parameters = $request->all();
        unset($parameters['group_by_map']);

        $children = [];

        foreach ($this->globalDataService->getMaps() as $map) {
            $childParameters = array_merge($parameters, ['game_map' => [$map->name]]);

            $children[$map->name] = [
                'cache_key' => GlobalCacheKey::withParameters($cacheKey, $childParameters),
                'request' => $childParameters,
            ];
        }

        return app(GlobalQueryService::class)->dispatchBatch(
            $cacheKey,
            $children,
            static::class,
            $executeMethod,
            $this->globalDataService->calculateCacheTimeInSeconds($gameVersion),
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
