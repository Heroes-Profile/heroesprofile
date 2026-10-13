<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * On-demand deep analytics for a player's season games.
 *
 * A request queues every season game not yet in replay_deep. The page answers right
 * away: it queues one Cloud Task per chunk of games to this app's fan-out route, and
 * each of those queues the chunk's deep parses on the replay parser's deep service.
 * The VM loader writes replay_deep once a game's rows are in BigQuery, so progress is
 * a count of those rows.
 */
class DeepAnalyticsService
{
    public function __construct(private CloudTasksDispatcher $tasks) {}

    public function isAllowed(int $blizzId): bool
    {
        return in_array($blizzId, config('deep_analytics.allowlist'), true);
    }

    /** @return array{total: int, done: int} */
    public function status(int $blizzId, int $region): array
    {
        $row = $this->seasonGames($blizzId, $region)
            ->leftJoin('replay_deep', 'replay_deep.replayID', '=', 'replay.replayID')
            ->selectRaw('COUNT(*) AS total, COUNT(replay_deep.replayID) AS done')
            ->first();

        return ['total' => (int) $row->total, 'done' => (int) $row->done];
    }

    /**
     * Queues the player's unprocessed season games. A second request inside the lock
     * window queues nothing, so refreshing or double-clicking costs nothing.
     *
     * @return array{queued: int, already_requested: bool}
     */
    public function request(int $blizzId, int $region): array
    {
        if (! Cache::add('deep_analytics:request:'.$region.':'.$blizzId, 1, config('deep_analytics.request_lock_seconds'))) {
            return ['queued' => 0, 'already_requested' => true];
        }

        $pending = $this->seasonGames($blizzId, $region)
            ->leftJoin('replay_deep', 'replay_deep.replayID', '=', 'replay.replayID')
            ->whereNull('replay_deep.replayID')
            ->orderBy('replay.replayID')
            ->pluck('replay.replayID')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach (array_chunk($pending, config('deep_analytics.fanout_chunk')) as $chunk) {
            $this->tasks->dispatchHttp(
                url: $this->fanoutUrl(),
                body: ['replay_ids' => $chunk],
                queue: config('deep_analytics.queue'),
            );
        }

        return ['queued' => count($pending), 'already_requested' => false];
    }

    /**
     * Run by the fan-out task: queues one deep parse per replay, reading it from its
     * archive bundle or, if it was parsed too recently to be archived, the upload bucket.
     *
     * @param  array<int>  $replayIds
     */
    public function fanOut(array $replayIds): int
    {
        if ($replayIds === []) {
            return 0;
        }

        $archived = DB::connection('heroesprofile')->table('replay_archive')
            ->whereIn('replayID', $replayIds)
            ->get(['replayID', 'bundle', 'byte_offset', 'byte_length'])
            ->keyBy('replayID');

        $fingerprints = DB::connection('heroesprofile')->table('replay_fingerprints')
            ->whereIn('replayID', $replayIds)
            ->pluck('fingerprint', 'replayID');

        $serviceUrl = config('deep_analytics.service_url');
        $queued = 0;

        foreach ($replayIds as $replayId) {
            if ($archive = $archived->get($replayId)) {
                $body = [
                    'parseType' => 'deep',
                    'replayID' => $replayId,
                    'bucket' => config('deep_analytics.archive_bucket'),
                    'input' => $archive->bundle,
                    'offset' => (int) $archive->byte_offset,
                    'length' => (int) $archive->byte_length,
                ];
            } elseif ($fingerprint = $fingerprints->get($replayId)) {
                $body = [
                    'parseType' => 'deep',
                    'replayID' => $replayId,
                    'bucket' => config('deep_analytics.replay_bucket'),
                    'input' => $fingerprint.'.StormReplay',
                ];
            } else {
                continue;
            }

            $this->tasks->dispatchHttp(
                url: $serviceUrl,
                body: $body,
                queue: config('deep_analytics.queue'),
                audience: $serviceUrl,
            );
            $queued++;
        }

        return $queued;
    }

    private function seasonGames(int $blizzId, int $region)
    {
        return DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->where('player.blizz_id', $blizzId)
            ->where('replay.region', $region)
            ->where('replay.game_date', '>=', config('deep_analytics.cutover'));
    }

    // Same host the global query tasks already reach this app on, so the callback takes the
    // path Cloud Tasks is known to get through.
    private function fanoutUrl(): string
    {
        if ($url = config('deep_analytics.fanout_url')) {
            return (string) $url;
        }

        $handler = parse_url((string) config('global.cloud_tasks.handler_url'));

        return isset($handler['scheme'], $handler['host'])
            ? $handler['scheme'].'://'.$handler['host'].(isset($handler['port']) ? ':'.$handler['port'] : '').'/api/v1/internal/deep/fanout'
            : url('/api/v1/internal/deep/fanout');
    }
}
