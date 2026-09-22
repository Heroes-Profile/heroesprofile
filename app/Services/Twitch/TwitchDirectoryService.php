<?php

namespace App\Services\Twitch;

use App\Models\Api\TwitchChannel;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The public list of streamers using the extension.
 *
 * Only channels that opted in, accepted the current code of conduct, are not
 * hidden or suspended, and currently have access. Live channels come first, with
 * stream details from Helix; the whole list is cached and rebuilt by one request
 * at a time, so a busy page costs Twitch and the database one refresh every few
 * minutes.
 */
class TwitchDirectoryService
{
    private const CACHE_KEY = 'twitch_directory';

    public function __construct(
        private readonly TwitchHelix $helix,
        private readonly TwitchEntitlementService $entitlements,
    ) {}

    /** @return array{live: array<int, array<string, mixed>>, offline: array<int, array<string, mixed>>, updated_at: string} */
    public function list(): array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        // Whoever loses the race serves an empty list rather than piling onto Helix.
        $lock = Cache::lock(self::CACHE_KEY.':refresh', 30);

        if (! $lock->get()) {
            return Cache::get(self::CACHE_KEY) ?? ['live' => [], 'offline' => [], 'updated_at' => now()->toIso8601String()];
        }

        try {
            $directory = $this->build();
            Cache::put(self::CACHE_KEY, $directory, (int) config('twitch.directory_cache_seconds'));

            return $directory;
        } finally {
            $lock->release();
        }
    }

    /** @return array{live: array<int, array<string, mixed>>, offline: array<int, array<string, mixed>>, updated_at: string} */
    private function build(): array
    {
        $channels = TwitchChannel::with('account')
            ->where('listing_opt_in', true)
            ->where('listing_terms_version', (int) config('twitch.listing_terms_version'))
            ->whereNull('listing_hidden_at')
            ->whereNull('suspended_at')
            ->whereNotNull('user_id')
            ->get()
            ->filter(fn (TwitchChannel $channel) => $this->isActive($channel))
            ->keyBy('twitch_user_id');

        $streams = [];

        if ($channels->isNotEmpty()) {
            try {
                // Channels with the extension active and live, intersected with ours.
                $liveIds = collect($this->helix->liveExtensionChannels())
                    ->pluck('broadcaster_id')
                    ->map(fn ($id) => (string) $id)
                    ->intersect($channels->keys())
                    ->values()
                    ->all();

                $streams = $liveIds === [] ? [] : $this->helix->streams($liveIds);
            } catch (Throwable $e) {
                // Twitch being down should not take the page with it; everyone
                // shows as offline until the next refresh.
                report($e);
            }
        }

        $live = [];
        $offline = [];

        foreach ($channels as $channelId => $channel) {
            $entry = [
                'login' => $channel->twitch_login,
                'display_name' => $channel->twitch_display_name ?: $channel->twitch_login,
                'url' => 'https://www.twitch.tv/'.$channel->twitch_login,
                'battletag' => $channel->battletag ? explode('#', $channel->battletag)[0] : null,
                'blizz_id' => $channel->blizz_id,
                'region' => $channel->region,
            ];

            if (isset($streams[$channelId])) {
                $stream = $streams[$channelId];

                $live[] = $entry + [
                    'title' => $stream['title'] ?? null,
                    'viewer_count' => (int) ($stream['viewer_count'] ?? 0),
                    'started_at' => $stream['started_at'] ?? null,
                    'game_name' => $stream['game_name'] ?? null,
                    'thumbnail_url' => isset($stream['thumbnail_url'])
                        ? str_replace(['{width}', '{height}'], ['440', '248'], $stream['thumbnail_url'])
                        : null,
                ];
            } else {
                $offline[] = $entry;
            }
        }

        usort($live, fn ($a, $b) => $b['viewer_count'] <=> $a['viewer_count']);
        usort($offline, fn ($a, $b) => strcasecmp($a['display_name'], $b['display_name']));

        return ['live' => $live, 'offline' => $offline, 'updated_at' => now()->toIso8601String()];
    }

    private function isActive(TwitchChannel $channel): bool
    {
        try {
            return $this->entitlements->for($channel)->isActive();
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
