<?php

namespace App\Support;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Verifies a Google-signed OIDC token, such as the one Cloud Tasks attaches.
 *
 * Done with firebase/php-jwt rather than google/auth's AccessToken, which needs
 * phpseclib 3 and broke when an update moved phpseclib to 4.
 */
class GoogleIdToken
{
    private const CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    private const CACHE_KEY = 'google_oidc_certs';

    /** Google rotates its keys every few days and serves them for longer than this. */
    private const CACHE_SECONDS = 3600;

    /** Anyone can send us a token with a made-up key id, so refetching is rate-limited. */
    private const REFETCH_AFTER_SECONDS = 300;

    private const ISSUERS = ['https://accounts.google.com', 'accounts.google.com'];

    /**
     * The token's claims, or null if it is not a valid Google token for $audience.
     * Signature, expiry and not-before are checked by JWT::decode.
     *
     * @throws RuntimeException when Google's keys cannot be fetched
     */
    public static function verify(string $token, string $audience): ?array
    {
        $certs = self::certs();
        $claims = self::decode($token, $certs['jwks'], $unknownKey);

        // Google rotated and we have not seen the new key yet.
        if ($claims === null && $unknownKey && time() - $certs['fetched_at'] > self::REFETCH_AFTER_SECONDS) {
            $claims = self::decode($token, self::certs(fresh: true)['jwks'], $unknownKey);
        }

        if ($claims === null || ! in_array($claims['iss'] ?? null, self::ISSUERS, true)) {
            return null;
        }

        $aud = $claims['aud'] ?? null;

        if (is_array($aud) ? ! in_array($audience, $aud, true) : $aud !== $audience) {
            return null;
        }

        return $claims;
    }

    private static function decode(string $token, array $jwks, ?bool &$unknownKey): ?array
    {
        $unknownKey = false;

        try {
            return json_decode(json_encode(JWT::decode($token, JWK::parseKeySet($jwks, 'RS256'))), true);
        } catch (Throwable $e) {
            $unknownKey = str_contains($e->getMessage(), '"kid" invalid');

            return null;
        }
    }

    /** @return array{jwks: array, fetched_at: int} */
    private static function certs(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, function () {
            $response = Http::timeout(5)->get(self::CERTS_URL);

            if (! $response->successful() || ! is_array($response->json('keys'))) {
                throw new RuntimeException('Could not fetch Google OIDC certificates.');
            }

            return ['jwks' => $response->json(), 'fetched_at' => time()];
        });
    }
}
