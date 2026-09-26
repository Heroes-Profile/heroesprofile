<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * When each global stats result was computed, kept beside it under its own key.
 *
 * Judged against the window at read time rather than fixed when written, so a
 * patch that has just been replaced goes stale straight away.
 */
class GlobalCacheFreshness
{
    public static function key(string $cacheKey): string
    {
        return 'global_computed_at:'.hash('sha256', $cacheKey);
    }

    public static function stamp(string $cacheKey, int $ttl): void
    {
        if ($ttl <= 0) {
            return;
        }

        Cache::store('database')->put(self::key($cacheKey), time(), $ttl);
    }

    /** No stamp counts as stale: anything cached before stamps existed. */
    public static function isStale(string $cacheKey, GlobalCacheWindow $window): bool
    {
        if ($window->fresh === null) {
            return false;
        }

        $computedAt = Cache::store('database')->get(self::key($cacheKey));

        return $computedAt === null || time() - (int) $computedAt >= $window->fresh;
    }
}
