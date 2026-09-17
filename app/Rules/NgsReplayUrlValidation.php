<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * An NGS replay URL must point at the bucket NGS publishes to.
 *
 * The old API fetched whatever URL it was given. Server-side fetches of caller
 * supplied URLs are SSRF: on Cloud Run, `169.254.169.254` returns the service
 * account's access token, and that account can write to every bucket. Pinning the
 * host and bucket removes the class of attack rather than filtering for it.
 */
class NgsReplayUrlValidation implements Rule
{
    private const PATH_STYLE_HOST = 's3.amazonaws.com';

    public function passes($attribute, $value)
    {
        if (! is_string($value)) {
            return false;
        }

        $parts = parse_url($value);

        if (($parts['scheme'] ?? null) !== 'https' || ! isset($parts['host'], $parts['path'])) {
            return false;
        }

        $bucket = config('api.ngs.replay_bucket');

        // Both forms follow the one bucket setting, so changing it cannot leave a
        // stale host allowed.
        if (! in_array($parts['host'], [self::PATH_STYLE_HOST, $bucket.'.'.self::PATH_STYLE_HOST], true)) {
            return false;
        }

        // Raw segments, empty ones kept: `..` and `.` would let a path-style URL
        // walk out of the bucket once the HTTP client normalises it.
        $raw = explode('/', ltrim($parts['path'], '/'));

        foreach ($raw as $segment) {
            if (in_array(rawurldecode($segment), ['.', '..'], true)) {
                return false;
            }
        }

        $segments = array_values(array_filter($raw, fn ($segment) => $segment !== ''));

        if ($segments === []) {
            return false;
        }

        // Path-style URLs carry the bucket as the first segment; the virtual-host
        // form has it in the hostname already.
        if ($parts['host'] === self::PATH_STYLE_HOST) {
            return array_shift($segments) === $bucket && $segments !== [];
        }

        return true;
    }

    public function message()
    {
        return 'The replay URL must point at the NGS replay bucket.';
    }
}
