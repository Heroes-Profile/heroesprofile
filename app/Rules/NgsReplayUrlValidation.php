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
    public function passes($attribute, $value)
    {
        if (! is_string($value)) {
            return false;
        }

        $parts = parse_url($value);

        if (($parts['scheme'] ?? null) !== 'https' || ! isset($parts['host'], $parts['path'])) {
            return false;
        }

        if (! in_array($parts['host'], config('api.ngs.replay_hosts'), true)) {
            return false;
        }

        $segments = array_values(array_filter(explode('/', $parts['path'])));

        if ($segments === []) {
            return false;
        }

        // Path-style URLs carry the bucket as the first segment; the virtual-host
        // form has it in the hostname already.
        if ($parts['host'] === 's3.amazonaws.com') {
            return array_shift($segments) === config('api.ngs.replay_bucket') && $segments !== [];
        }

        return true;
    }

    public function message()
    {
        return 'The replay URL must point at the NGS replay bucket.';
    }
}
