<?php

namespace App\Support;

/**
 * How long one global stats result is kept, and how long it counts as fresh.
 *
 * Past `fresh` the result is still served, and a refresh is queued behind it.
 * Null means it never goes stale: the timeframe is no longer receiving games.
 */
final class GlobalCacheWindow
{
    public function __construct(
        public readonly int $ttl,
        public readonly ?int $fresh,
    ) {}
}
