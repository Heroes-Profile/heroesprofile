<?php

namespace App\Services\Api;

use Illuminate\Http\Request;

/**
 * Whether a request was signed by a released build of the uploader.
 *
 * Each release build carries its own key, derived from a master secret the release workflow and this site
 * share: key = HMAC-SHA256(master, version). The build signs "{timestamp}\n{payload}" with it and sends the
 * version, timestamp and signature as headers (X-HP-Uploader-Version, X-HP-Timestamp, X-HP-Signature); this
 * recomputes the key from the claimed version and checks the signature. The key never travels, a request
 * can't be replayed outside the clock window, and a build from the public source has no key. The payload
 * is the request body - or, for a replay upload (multipart, which PHP gives no raw body for), the file's
 * SHA-256 in hex.
 *
 * A deterrent, not a lock: the key is inside every installed copy and can be dug out. A leaked version is
 * cut off with RANKS_DENIED_VERSIONS (or RANKS_MINIMUM_VERSION); its users update to a fresh key.
 */
class UploaderSignature
{
    /** Whether the site has a signing secret at all; without one nothing can be checked. */
    public function configured(): bool
    {
        return (string) config('api.ranks.signing_secret') !== '';
    }

    /** Why the request isn't validly signed, or null when it is. */
    public function problem(Request $request, string $payload): ?string
    {
        $config = config('api.ranks');
        $secret = (string) ($config['signing_secret'] ?? '');
        if ($secret === '') {
            return 'Not accepting signed requests';
        }

        $version = (string) $request->header('X-HP-Uploader-Version', '');
        $timestamp = (string) $request->header('X-HP-Timestamp', '');
        $signature = strtolower((string) $request->header('X-HP-Signature', ''));

        if ($version === '' || strlen($version) > 45 || ! ctype_digit($timestamp) || ! ctype_xdigit($signature)) {
            return 'Unsigned request';
        }
        if (in_array($version, $config['denied_versions'], true)
            || version_compare($version, (string) $config['minimum_version'], '<')) {
            return 'This uploader version can no longer send this; please update';
        }
        if (abs(time() - (int) $timestamp) > (int) $config['max_clock_skew']) {
            return 'Request too old or clock off';
        }

        $key = hash_hmac('sha256', $version, $secret, true);
        $expected = hash_hmac('sha256', $timestamp."\n".$payload, $key);

        return hash_equals($expected, $signature) ? null : 'Bad signature';
    }
}
