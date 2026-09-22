<?php

namespace App\Services\Twitch;

use App\Models\Api\TwitchChannel;
use App\Services\CloudTasksDispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Hands one update to Twitch, which delivers it to every viewer.
 *
 * Two calls per update, however many people are watching:
 *
 *  - Extension PubSub, with the whole payload as the message — every open
 *    extension applies it directly, no request back to us.
 *  - The developer configuration segment, with the same payload — Twitch gives it
 *    to anyone who opens the extension mid-game, from its own CDN.
 *
 * With a delay, the update is carried inside a Cloud Task scheduled for the moment
 * it may be shown. Production runs a sync queue, so a delayed job would run at
 * once; Cloud Tasks is what actually waits.
 */
class TwitchPushService
{
    private const TTL_SECONDS = 10800;

    public function __construct(
        private readonly TwitchHelix $helix,
        private readonly CloudTasksDispatcher $tasks,
    ) {}

    public function schedule(TwitchChannel $channel, string $gameId, int $seq, string $payload, int $delaySeconds): void
    {
        $channelId = (string) $channel->twitch_user_id;

        if ($delaySeconds > 0 && $this->canSchedule()) {
            try {
                $this->tasks->dispatchHttp(
                    url: $this->handlerUrl(),
                    body: ['channel_id' => $channelId, 'game_id' => $gameId, 'seq' => $seq, 'payload' => $payload],
                    queue: (string) config('twitch.push_queue'),
                    notBefore: now()->addSeconds($delaySeconds),
                );

                return;
            } catch (Throwable $e) {
                // Never fall back to sending it now: that would show a delayed
                // streamer's game to viewers early. Dropping one update is harmless,
                // since the next carries the whole game again.
                report($e);

                return;
            }
        }

        if ($delaySeconds > 0) {
            Log::warning('Twitch push with a delay but Cloud Tasks is not configured; update dropped.', ['channel_id' => $channelId]);

            return;
        }

        // No delay: after the response, so the uploader is not kept waiting on Twitch.
        app()->terminating(fn () => $this->publish($channelId, $gameId, $seq, $payload));
    }

    /**
     * Sends it, unless a newer update for the same game has already gone out.
     * Tasks can fire out of order; an older full state must never replace a newer.
     */
    public function publish(string $channelId, string $gameId, int $seq, string $payload): bool
    {
        $key = 'twitch_published:'.$channelId;
        $published = Cache::get($key);

        if (is_array($published) && $published['game_id'] === $gameId && $seq <= $published['seq']) {
            return false;
        }

        Cache::put($key, ['game_id' => $gameId, 'seq' => $seq], self::TTL_SECONDS);

        try {
            $this->helix->sendPubSub($channelId, $payload);
            $this->helix->setDeveloperConfiguration($channelId, $payload, (string) $seq);
        } catch (Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }

    /**
     * Tells viewers the extension is not active for this channel. Once per lapse,
     * not once per snapshot the uploader keeps sending.
     */
    public function publishInactive(TwitchChannel $channel): void
    {
        $channelId = (string) $channel->twitch_user_id;

        if (! Cache::add('twitch_inactive_sent:'.$channelId, true, self::TTL_SECONDS)) {
            return;
        }

        Cache::forget('twitch_published:'.$channelId);

        app()->terminating(function () use ($channelId) {
            try {
                $payload = TwitchPayload::inactive();
                $this->helix->sendPubSub($channelId, $payload);
                $this->helix->setDeveloperConfiguration($channelId, $payload, 'inactive');
            } catch (Throwable $e) {
                report($e);
            }
        });
    }

    /** Called when a channel is active again, so a later lapse is announced too. */
    public function clearInactive(TwitchChannel $channel): void
    {
        Cache::forget('twitch_inactive_sent:'.$channel->twitch_user_id);
    }

    private function canSchedule(): bool
    {
        return (bool) config('global.cloud_tasks.project_id')
            && (bool) config('global.cloud_tasks.handler_url')
            && (bool) config('global.cloud_tasks.service_account');
    }

    private function handlerUrl(): string
    {
        return (string) (config('twitch.push_handler_url') ?: url('/api/twitch/v1/internal/push'));
    }
}
