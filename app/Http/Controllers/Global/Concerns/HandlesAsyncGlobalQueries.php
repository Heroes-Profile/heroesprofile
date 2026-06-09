<?php

namespace App\Http\Controllers\Global\Concerns;

use App\Services\GlobalAsyncQueryService;
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
    ): JsonResponse {
        if (! $this->globalDataService->isGlobalAsyncEnabled()) {
            Cache::store('database')->forget($cacheKey);
        }

        [$cacheFreshSeconds, $cacheStaleSeconds] = $this->globalDataService->getCacheFreshAndStaleSeconds($gameVersion);

        return app(GlobalAsyncQueryService::class)->handle(
            $cacheKey,
            static::class,
            $executeMethod,
            $request->all(),
            $cacheFreshSeconds,
            $cacheStaleSeconds
        );
    }
}
