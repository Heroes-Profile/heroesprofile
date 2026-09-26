<?php

namespace App\Services\Twitch;

/**
 * The message viewers receive, as compact JSON.
 *
 * Twitch caps an Extension PubSub message, and a configuration segment, at 5KB.
 * A full lobby with stats fits in roughly half that because only ids and numbers
 * are sent; the extension looks the rest up in its bundled game data.
 *
 * Shape (version 1):
 *
 *   { v: 1, g: game_id, s: seq, p: phase, t: game mode, m: map, c: channel name, pl: [player…] }
 *
 *   player: [team, name|null, blizz_id|null, region|null, hero_id|null,
 *            [7 talent ids, 0 = not picked yet], stats|null, 1 if an A.I.]
 *            (the last element is only present for A.I. players)
 *
 *   stats:  { l: account level, q|s|a: [mmr, rank, win rate, games] }  (quick match,
 *           storm league, aram; a mode the player has no rating in is omitted)
 *
 * The streamer's team is always team 0.
 */
final class TwitchPayload
{
    public const VERSION = 1;

    /**
     * @param  array<int, array<string, mixed>>  $players  normalised players, see TwitchSnapshotService
     */
    public static function encode(string $gameId, int $seq, string $phase, ?string $mode, ?string $map, array $players, int $maxBytes, ?string $channelName = null): string
    {
        $message = [
            'v' => self::VERSION,
            'g' => $gameId,
            's' => $seq,
            'p' => $phase,
            't' => $mode,
            'm' => $map,
            // Names the streamer's team in the extension.
            'c' => $channelName,
            'pl' => array_map(fn ($player) => array_merge([
                $player['team'],
                $player['name'],
                $player['blizz_id'],
                $player['region'],
                $player['hero_id'],
                array_pad(array_slice(array_map('intval', $player['talents']), 0, 7), 7, 0),
                $player['stats'],
            ], ($player['ai'] ?? false) ? [1] : []), $players),
        ];

        $json = self::json($message);

        // Stats go first, least useful part first, until it fits. Heroes and
        // talents are the point of the extension and are never dropped.
        foreach ([[3], [2, 3], null] as $keep) {
            if (strlen($json) <= $maxBytes) {
                return $json;
            }

            foreach ($message['pl'] as &$player) {
                $player[6] = $keep === null ? null : self::trimStats($player[6], $keep);
            }
            unset($player);

            $json = self::json($message);
        }

        return $json;
    }

    /** Sent once when a channel's access has lapsed, so viewers see why it stopped. */
    public static function inactive(): string
    {
        return self::json(['v' => self::VERSION, 'inactive' => true]);
    }

    /**
     * Compact per-player stats from a PlayerLobbyStatsService row.
     *
     * @param  array<string, mixed>  $stats
     * @param  array{qm_rank: mixed, sl_rank: mixed, ar_rank: mixed}  $ranks
     * @return array<string, mixed>
     */
    public static function stats(array $stats, array $ranks): array
    {
        $compact = ['l' => $stats['account_level'] ?? null];

        foreach (['qm' => 'q', 'sl' => 's', 'ar' => 'a'] as $mode => $key) {
            $games = (int) ($stats[$mode.'_games_played'] ?? 0);

            if ($stats[$mode.'_mmr'] === null && $games === 0) {
                continue;
            }

            $compact[$key] = [
                $stats[$mode.'_mmr'],
                self::rankName($ranks[$mode.'_rank'] ?? null),
                $stats[$mode.'_win_rate'] === null ? null : round((float) $stats[$mode.'_win_rate'], 1),
                $games,
            ];
        }

        return $compact;
    }

    /**
     * Drops trailing fields of each mode, keeping only the given positions.
     *
     * @param  array<string, mixed>|null  $stats
     * @param  array<int, int>  $drop  positions to null out
     */
    private static function trimStats(?array $stats, array $drop): ?array
    {
        if ($stats === null) {
            return null;
        }

        foreach (['q', 's', 'a'] as $mode) {
            if (isset($stats[$mode])) {
                foreach ($drop as $position) {
                    $stats[$mode][$position] = null;
                }
            }
        }

        return $stats;
    }

    private static function rankName(mixed $rank): ?string
    {
        if (is_string($rank)) {
            return $rank;
        }

        if (is_array($rank)) {
            return $rank['name'] ?? $rank['rank'] ?? null;
        }

        if (is_object($rank)) {
            return $rank->name ?? $rank->rank ?? null;
        }

        return null;
    }

    private static function json(array $message): string
    {
        return json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
