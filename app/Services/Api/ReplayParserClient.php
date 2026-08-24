<?php

namespace App\Services\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * Client for the replay parser service.
 *
 * The parser reads the replay straight out of the bucket, so only its key and
 * bucket are sent — never the file itself.
 *
 * Authentication is Cloud Run service-to-service: an ID token minted for the
 * parser's URL as audience. The token comes from the metadata server rather than
 * ADC, because the audience has to be the receiving service and ADC does not
 * express that. Locally there is no metadata server, so no header is sent and the
 * parser is expected to be reachable unauthenticated.
 */
class ReplayParserClient
{
    private const METADATA_HOST = 'http://metadata.google.internal';

    private const IDENTITY_PATH = '/computeMetadata/v1/instance/service-accounts/default/identity';

    /** Refresh a little before expiry rather than on it. */
    private const EXPIRY_GRACE_SECONDS = 30;

    private static ?string $token = null;

    private static ?int $tokenExpiresAt = null;

    public function __construct(
        private readonly Client $client = new Client,
        private readonly ?Client $metadata = null,
    ) {}

    /**
     * @return array<string, mixed> the parsed replay, or `error` describing why not
     */
    public function parse(string $key, string $bucket, string $parseType = 'default', string $fingerprint = ''): array
    {
        $endpoint = (string) config('services.replay_parser.url');

        if ($endpoint === '') {
            return ['error' => 'No replay parser configured.'];
        }

        $response = $this->client->post($endpoint, [
            'http_errors' => false,
            'headers' => $this->authHeaders(self::audienceFor($endpoint)),
            'json' => [
                'input' => $key,
                'fingerprint' => $fingerprint,
                'parseType' => $parseType,
                'bucket' => $bucket,
            ],
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            // `http_errors` is off, so Guzzle throws nothing here — without this
            // report a parser outage would be invisible to Flare.
            report(new RuntimeException(
                "Replay parser returned HTTP {$status} for [{$key}] in [{$bucket}] (parseType {$parseType}): {$body}"
            ));

            return ['error' => $body, 'status' => $status];
        }

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // A replay the parser refuses is the caller's file being unusable, not a
            // fault here — incomplete replays arrive all day and the upload already
            // answers with a failure status. Reporting each one buries the outages
            // this report exists to catch.
            if (! str_starts_with(trim($body), 'Error parsing replay')) {
                report(new RuntimeException(
                    "Replay parser returned non-JSON for [{$key}] in [{$bucket}]: {$body}"
                ));
            }

            return ['error' => $body];
        }

        return $decoded;
    }

    /** @return array<string, string> */
    /**
     * The audience a Cloud Run identity token must carry: the service's base URL,
     * scheme and host only.
     *
     * Minting it from the full endpoint instead — path included — produces a token
     * Cloud Run rejects for audience mismatch, answered as a Google 403 HTML page
     * rather than anything the parser wrote. Indistinguishable from a missing
     * invoker role unless you decode the token.
     */
    private static function audienceFor(string $endpoint): string
    {
        $parts = parse_url($endpoint);

        if (! isset($parts['scheme'], $parts['host'])) {
            return $endpoint;
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port;
    }

    private function authHeaders(string $audience): array
    {
        if (app()->environment('local', 'development')) {
            return [];
        }

        try {
            return ['Authorization' => 'Bearer '.$this->idToken($audience)];
        } catch (RuntimeException $e) {
            // Without a token the parser will reject the call, but failing here
            // would lose the more useful error from the parser itself. Reported
            // rather than logged, so a broken metadata server is not silent.
            report($e);

            return [];
        }
    }

    private function idToken(string $audience): string
    {
        $now = time();

        if (self::$token !== null && self::$tokenExpiresAt !== null
            && ($now + self::EXPIRY_GRACE_SECONDS) < self::$tokenExpiresAt) {
            return self::$token;
        }

        $token = $this->fetchIdToken($audience);

        self::$token = $token;
        self::$tokenExpiresAt = $this->expiryOf($token);

        return $token;
    }

    private function fetchIdToken(string $audience): string
    {
        $client = $this->metadata ?? new Client([
            'base_uri' => self::METADATA_HOST,
            'timeout' => 2.5,
            'connect_timeout' => 2.5,
        ]);

        try {
            // Absolute, not relying on the client's `base_uri`. The container fills
            // the nullable `$metadata` constructor argument with a bare Guzzle client
            // — it is a concrete class, so the null default never applies — and that
            // client has no base, which made every token fetch fail and every parse
            // go out unauthenticated.
            $response = $client->get(self::METADATA_HOST.self::IDENTITY_PATH, [
                'headers' => ['Metadata-Flavor' => 'Google'],
                'query' => ['audience' => $audience, 'format' => 'full'],
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Metadata token fetch failed: '.$e->getMessage(), 0, $e);
        }

        $token = trim((string) $response->getBody());

        if ($token === '') {
            throw new RuntimeException('Metadata server returned an empty token.');
        }

        return $token;
    }

    /** Reads `exp` out of the JWT so the token is reused until it actually expires. */
    private function expiryOf(string $jwt): ?int
    {
        $parts = explode('.', $jwt);

        if (count($parts) < 2) {
            return null;
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return null;
        }

        $claims = json_decode($decoded, true);

        return is_array($claims) && isset($claims['exp']) && is_numeric($claims['exp'])
            ? (int) $claims['exp']
            : null;
    }
}
