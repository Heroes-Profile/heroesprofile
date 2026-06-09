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

        if (! $this->globalDataService->isGlobalAsyncEnabled()) {
            if (config('app.env') !== 'production') {
                Cache::store('database')->forget($cacheKey);
            }

            $data = Cache::store('database')->remember($cacheKey, $cacheTtlSeconds, function () use ($request, $executeMethod) {
                return app(static::class)->{$executeMethod}($request);
            });

            return $data;
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
