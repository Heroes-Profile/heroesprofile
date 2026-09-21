<?php

namespace App\Services\Twitch;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * The handful of Helix endpoints the extension uses.
 *
 * Extension endpoints (PubSub, configuration) are authorised with an external JWT
 * signed with the extension secret. Discovery endpoints (live channels, streams)
 * use an app access token from the client-credentials grant.
 */
class TwitchHelix
{
    private const BASE = 'https://api.twitch.tv/helix/';

    private const APP_TOKEN_CACHE = 'twitch_app_access_token';

    public function __construct(private readonly TwitchJwt $jwt) {}

    /**
     * Sends a message to every viewer of one channel. Twitch fans it out; nothing
     * comes back to us per viewer.
     */
    public function sendPubSub(string $channelId, string $message): void
    {
        $this->extensionRequest($channelId)
            ->post(self::BASE.'extensions/pubsub', [
                'target' => ['broadcast'],
                'broadcaster_id' => $channelId,
                'is_global_broadcast' => false,
                'message' => $message,
            ])
            ->throw();
    }

    /**
     * Writes the developer configuration segment for one channel. Twitch hands it
     * to every viewer when the extension loads, which is how someone opening it
     * mid-game sees the current state without asking us.
     */
    public function setDeveloperConfiguration(string $channelId, string $content, string $version): void
    {
        $this->extensionRequest($channelId)
            ->put(self::BASE.'extensions/configurations', [
                'extension_id' => config('twitch.client_id'),
                'segment' => 'developer',
                'broadcaster_id' => $channelId,
                'version' => $version,
                'content' => $content,
            ])
            ->throw();
    }

    /**
     * Channels that currently have the extension active and are live. Paginated
     * by Twitch; all pages are followed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function liveExtensionChannels(): array
    {
        $channels = [];
        $cursor = null;

        do {
            $response = $this->appRequest()
                ->get(self::BASE.'extensions/live', array_filter([
                    'extension_id' => config('twitch.client_id'),
                    'first' => 100,
                    'after' => $cursor,
                ]))
                ->throw();

            $channels = array_merge($channels, $response->json('data') ?? []);
            $cursor = $response->json('pagination');
            // This endpoint returns the cursor as a bare string, unlike most of Helix.
            $cursor = is_array($cursor) ? ($cursor['cursor'] ?? null) : ($cursor ?: null);
        } while ($cursor !== null && count($channels) < 5000);

        return $channels;
    }

    /**
     * Live stream details for up to 100 broadcasters per call.
     *
     * @param  array<int, string>  $userIds
     * @return array<string, array<string, mixed>> keyed by broadcaster id
     */
    public function streams(array $userIds): array
    {
        $streams = [];

        foreach (array_chunk(array_values(array_unique($userIds)), 100) as $chunk) {
            $query = implode('&', array_map(fn ($id) => 'user_id='.urlencode($id), $chunk)).'&first=100';

            $response = $this->appRequest()->get(self::BASE.'streams?'.$query)->throw();

            foreach ($response->json('data') ?? [] as $stream) {
                $streams[(string) $stream['user_id']] = $stream;
            }
        }

        return $streams;
    }

    private function extensionRequest(string $channelId): PendingRequest
    {
        return Http::timeout(5)
            ->retry(2, 250, fn ($exception) => $this->isRetryable($exception), throw: false)
            ->withHeaders(['Client-Id' => $this->clientId()])
            ->withToken($this->jwt->signExternal($channelId));
    }

    private function appRequest(): PendingRequest
    {
        return Http::timeout(5)
            ->withHeaders(['Client-Id' => $this->clientId()])
            ->withToken($this->appAccessToken());
    }

    private function appAccessToken(): string
    {
        $cached = Cache::get(self::APP_TOKEN_CACHE);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post('https://id.twitch.tv/oauth2/token', [
                'client_id' => $this->clientId(),
                'client_secret' => config('twitch.client_secret'),
                'grant_type' => 'client_credentials',
            ])
            ->throw();

        $token = (string) $response->json('access_token');
        // Refreshed a minute early so a token is never used in its last seconds.
        $ttl = max(60, (int) $response->json('expires_in', 3600) - 60);

        Cache::put(self::APP_TOKEN_CACHE, $token, $ttl);

        return $token;
    }

    private function isRetryable(mixed $exception): bool
    {
        $response = $exception->response ?? null;

        return ! $response instanceof Response || $response->serverError() || $response->status() === 429;
    }

    private function clientId(): string
    {
        $clientId = (string) config('twitch.client_id');

        if ($clientId === '') {
            throw new RuntimeException('TWITCH_EXTENSION_CLIENT_ID is not set.');
        }

        return $clientId;
    }
}
