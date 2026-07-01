<?php

namespace App\Http\Controllers\Concerns;

use App\Services\GlobalQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

trait HandlesAsyncQueries
{
    protected function asyncResponse(
        Request $request,
        string $cacheKey,
        string $executeMethod,
        int $cacheTtlSeconds
    ): JsonResponse|array {
        $cache = Cache::store('database');

        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return response()->json($cached)
                ->header('X-Cache-Status', 'fresh');
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
