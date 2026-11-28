<?php

namespace App\Support;

use App\Services\ClientIpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * The Talents page lists a player's heroes in one call, then asks for each uncached
 * hero's builds in turn. The list call grants one request per hero it asked for, and
 * those skip the site's per-minute limit, so one page load counts once.
 *
 * Keyed by IP, like the limiter for site calls (the session isn't started when it runs).
 */
class TalentBuildAllowance
{
    private const TTL_SECONDS = 300;

    /** @param  iterable<string>  $heroNames */
    public static function grant(Request $request, iterable $heroNames): void
    {
        $values = [];

        foreach ($heroNames as $heroName) {
            $values[self::key($request, $heroName)] = 1;
        }

        if ($values) {
            Cache::store('database')->putMany($values, self::TTL_SECONDS);
        }
    }

    /** Uses up the hero's request, if it was granted. */
    public static function consume(Request $request): bool
    {
        $hero = $request->input('hero');

        if (! is_string($hero) || $hero === '') {
            return false;
        }

        return Cache::store('database')->pull(self::key($request, $hero)) !== null;
    }

    private static function key(Request $request, string $heroName): string
    {
        return 'talent_build_allowance:'.hash('sha256', implode('|', [
            ClientIpService::getClientIp($request),
            $request->input('blizz_id'),
            $request->input('region'),
            $request->input('season') ? json_encode($request->input('season')) : '',
            $heroName,
        ]));
    }
}
