<?php

namespace App\Services\Twitch;

use App\Models\Api\TwitchChannel;
use App\Models\Battletag;
use App\Services\PlayerLobbyStatsService;
use Illuminate\Support\Facades\Cache;

/**
 * Turns an uploader snapshot into the message viewers see, and schedules it.
 *
 * Each snapshot is the whole game so far — lobby, heroes, every talent picked —
 * so a viewer who misses one is still right after the next. The database is only
 * touched once per game, for player identities and stats; every later snapshot in
 * that game is heroes and talents resolved from cached lookup tables.
 */
class TwitchSnapshotService
{
    private const TTL_SECONDS = 10800;

    /** uploader_last_seen_at is written at most this often. */
    private const SEEN_WRITE_SECONDS = 60;

    public function __construct(
        private readonly TwitchGameData $gameData,
        private readonly PlayerLobbyStatsService $lobbyStats,
        private readonly TwitchPushService $push,
    ) {}

    /**
     * @param  array<string, mixed>  $snapshot  validated request body
     * @return array{accepted: bool, seq: int}
     */
    public function ingest(TwitchChannel $channel, array $snapshot): array
    {
        $gameId = (string) $snapshot['game_id'];
        $seq = (int) $snapshot['seq'];
        $latest = Cache::get(self::liveKey($channel));

        // Out of order or repeated: an older full state carries nothing new.
        if (is_array($latest) && $latest['game_id'] === $gameId && $seq <= $latest['seq']) {
            return ['accepted' => false, 'seq' => (int) $latest['seq']];
        }

        $channel->startTrialIfUnstarted();
        $this->touchLastSeen($channel);

        $players = $this->players($channel, $gameId, $snapshot);

        $payload = TwitchPayload::encode(
            gameId: $gameId,
            seq: $seq,
            phase: (string) $snapshot['phase'],
            mode: $snapshot['game_mode'] ?? null,
            map: $snapshot['map'] ?? null,
            players: $players,
            maxBytes: (int) config('twitch.max_payload_bytes'),
        );

        Cache::put(self::liveKey($channel), [
            'game_id' => $gameId,
            'seq' => $seq,
            'received_at' => now()->toIso8601String(),
            'payload' => $payload,
        ], self::TTL_SECONDS);

        // The delay is applied here, before anything leaves the building. There is
        // no endpoint serving viewers early data to go around it.
        $this->push->schedule($channel, $gameId, $seq, $payload, (int) $channel->delay_seconds);

        return ['accepted' => true, 'seq' => $seq];
    }

    /** The newest snapshot, undelayed, for the broadcaster's own view. */
    public function latest(TwitchChannel $channel): ?array
    {
        $latest = Cache::get(self::liveKey($channel));

        return is_array($latest) ? $latest : null;
    }

    /**
     * Normalised players with heroes and talents for this snapshot. Identity and
     * stats come from the per-game cache after the first snapshot of a game.
     *
     * @return array<int, array<string, mixed>>
     */
    private function players(TwitchChannel $channel, string $gameId, array $snapshot): array
    {
        $roster = $this->roster($channel, $gameId, $snapshot['players']);

        $players = [];

        foreach ($snapshot['players'] as $index => $player) {
            $known = $roster['players'][$index] ?? null;
            $heroId = $this->gameData->heroId($player['hero'] ?? null);
            // Private and banned players keep their hero and talents — those are on
            // the stream anyway — and nothing that identifies them.
            $hidden = (bool) ($known['hidden'] ?? false);

            $players[] = [
                // Flipped so the streamer's team is always first.
                'team' => $roster['flip'] ? 1 - (int) $player['team'] : (int) $player['team'],
                'name' => $hidden ? null : $player['name'],
                'blizz_id' => $hidden ? null : ($known['blizz_id'] ?? null),
                'region' => $hidden ? null : ($known['region'] ?? null),
                'hero_id' => $heroId,
                'talents' => $this->talentIds($heroId, $player['talents'] ?? []),
                'stats' => $channel->show_stats ? ($known['stats'] ?? null) : null,
            ];
        }

        // Streamer's team first, then the order the game lists them in.
        usort($players, fn ($a, $b) => $a['team'] <=> $b['team']);

        return $players;
    }

    /**
     * Who is in the lobby, resolved once per game: blizz ids from our own
     * battletags (never the client's), privacy, stats, and which side the streamer
     * is on. A lobby does not change mid-game, so every later snapshot reuses this.
     *
     * @param  array<int, array<string, mixed>>  $players
     * @return array{flip: bool, players: array<int, array<string, mixed>>}
     */
    private function roster(TwitchChannel $channel, string $gameId, array $players): array
    {
        $signature = md5(json_encode(array_map(fn ($p) => [$p['name'], $p['battletag'], $p['region'], $p['team']], $players)));
        $cacheKey = 'twitch_roster:'.$channel->twitch_user_id.':'.$gameId;
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && $cached['signature'] === $signature) {
            return $cached['roster'];
        }

        $battletags = array_map(fn ($p) => $p['name'].'#'.$p['battletag'], $players);

        $blizzIds = Battletag::whereIn('battletag', $battletags)
            ->whereIn('region', array_unique(array_column($players, 'region')))
            ->orderBy('latest_game')
            ->get(['battletag', 'region', 'blizz_id'])
            ->mapWithKeys(fn ($row) => [$row->battletag.'|'.$row->region => (int) $row->blizz_id]);

        $identities = collect($players)->map(fn ($p, $i) => (object) [
            'index' => $i,
            'blizz_id' => $blizzIds[$p['name'].'#'.$p['battletag'].'|'.$p['region']] ?? null,
            'region' => (int) $p['region'],
            'team' => (int) $p['team'],
        ]);

        $known = $identities->filter(fn ($p) => $p->blizz_id !== null)->values();

        ['stats' => $stats, 'hidden' => $hidden] = $known->isEmpty()
            ? ['stats' => [], 'hidden' => []]
            : $this->lobbyStats->forPlayers($known);

        $roster = ['flip' => false, 'players' => []];

        foreach ($identities as $identity) {
            $key = PlayerLobbyStatsService::key($identity);
            $row = $identity->blizz_id !== null ? ($stats[$key] ?? null) : null;

            $roster['players'][$identity->index] = [
                'blizz_id' => $identity->blizz_id,
                'region' => $identity->blizz_id !== null ? $identity->region : null,
                'hidden' => isset($hidden[$key]),
                'stats' => $row !== null && ! isset($hidden[$key])
                    ? TwitchPayload::stats($row, $this->lobbyStats->ranks($row))
                    : null,
            ];

            if ($channel->hasPlayerLinked()
                && $identity->blizz_id === $channel->blizz_id
                && $identity->region === $channel->region) {
                $roster['flip'] = $identity->team === 1;
            }
        }

        Cache::put($cacheKey, ['signature' => $signature, 'roster' => $roster], self::TTL_SECONDS);

        return $roster;
    }

    /**
     * @param  array<int, string|null>  $talentNames  in tier order
     * @return array<int, int>
     */
    private function talentIds(?int $heroId, array $talentNames): array
    {
        $heroName = $heroId !== null ? $this->gameData->heroName($heroId) : null;

        return array_map(
            fn ($name) => ($heroName !== null && is_string($name) && $name !== '')
                ? ($this->gameData->talentId($heroName, $name) ?? 0)
                : 0,
            array_slice($talentNames, 0, 7)
        );
    }

    private function touchLastSeen(TwitchChannel $channel): void
    {
        if ($channel->uploader_last_seen_at === null
            || $channel->uploader_last_seen_at->lt(now()->subSeconds(self::SEEN_WRITE_SECONDS))) {
            $channel->forceFill(['uploader_last_seen_at' => now()])->save();
        }
    }

    private static function liveKey(TwitchChannel $channel): string
    {
        return 'twitch_live:'.$channel->twitch_user_id;
    }
}
