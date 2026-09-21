<?php

namespace App\Services\Twitch;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use Throwable;

/**
 * Twitch extension JWTs, both directions.
 *
 * Incoming: the extension frontend receives a token from Twitch in `onAuthorized`
 * and sends it as `Authorization: Extension <jwt>`. It is HS256-signed with the
 * extension secret, so a valid signature proves Twitch issued it for that channel.
 * The channel is only ever read from here, never from a request parameter.
 *
 * Outgoing: Helix extension endpoints are authorised with a token we sign ourselves
 * with role `external`.
 */
class TwitchJwt
{
    private const ALGORITHM = 'HS256';

    private const LEEWAY_SECONDS = 30;

    /** @param  array<int, string>|null  $secrets  base64, current first; null reads config */
    public function __construct(
        private readonly ?array $secrets = null,
        private readonly ?string $ownerUserId = null,
    ) {}

    /**
     * The verified claims, or null when the token is not one Twitch issued for us.
     *
     * @return array<string, mixed>|null
     */
    public function verify(string $token): ?array
    {
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = self::LEEWAY_SECONDS;

        try {
            // Every configured secret, so a rotation does not log out every viewer
            // holding a token signed with the one being retired.
            foreach ($this->decodedSecrets() as $secret) {
                try {
                    $claims = (array) JWT::decode($token, new Key($secret, self::ALGORITHM));
                } catch (Throwable) {
                    continue;
                }

                if (! isset($claims['exp'], $claims['channel_id']) || ! is_string($claims['channel_id'])) {
                    return null;
                }

                return $claims;
            }

            return null;
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }

    /** A short-lived token for calling Helix extension endpoints for one channel. */
    public function signExternal(string $channelId): string
    {
        $secrets = $this->decodedSecrets();

        if ($secrets === []) {
            throw new RuntimeException('No Twitch extension secret is configured. Set TWITCH_EXTENSION_SECRETS.');
        }

        return JWT::encode([
            'exp' => time() + 60,
            'user_id' => $this->ownerUserId ?? (string) config('twitch.owner_user_id'),
            'role' => 'external',
            'channel_id' => $channelId,
            'pubsub_perms' => ['send' => ['broadcast']],
        ], $secrets[0], self::ALGORITHM);
    }

    /** @return array<int, string> */
    private function decodedSecrets(): array
    {
        $secrets = $this->secrets ?? config('twitch.extension_secrets', []);

        return array_values(array_filter(array_map(
            fn ($secret) => base64_decode((string) $secret, true) ?: null,
            $secrets
        )));
    }
}
