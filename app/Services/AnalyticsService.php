<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * On-demand analytics for a player's season games.
 *
 * A request queues every season game not yet in replay_deep. The page answers right
 * away: it queues one Cloud Task per chunk of games to this app's fan-out route, and
 * each of those queues the chunk's analytics parses on the replay parser's analytics service (replay-parser-deep).
 * The VM loader writes replay_deep once a game's rows are in BigQuery, so progress is
 * a count of those rows. Rows from an older extract version count as not done, so the
 * next request rebuilds them.
 */
class AnalyticsService
{
    public function __construct(private CloudTasksDispatcher $tasks) {}

    public function isAllowed(int $blizzId): bool
    {
        return in_array($blizzId, config('analytics.allowlist'), true);
    }

    /**
     * processing: a request is still working through the games, so a page opened later
     * picks up where it left off. Games that never finish (no replay file anywhere) would
     * keep it set, so it also runs out on its own.
     *
     * @return array{total: int, done: int, processing: bool}
     */
    public function status(int $blizzId, int $region): array
    {
        $row = $this->seasonGames($blizzId, $region)
            ->leftJoin('replay_deep', fn ($join) => $this->currentLoadedRow($join))
            ->selectRaw('COUNT(*) AS total, COUNT(replay_deep.replayID) AS done')
            ->first();
        $total = (int) $row->total;
        $done = (int) $row->done;

        $processing = Cache::has($this->processingKey($blizzId, $region));
        if ($processing && $done >= $total) {
            Cache::forget($this->processingKey($blizzId, $region));
            $processing = false;
        }

        return ['total' => $total, 'done' => $done, 'processing' => $processing];
    }

    /**
     * Queues the player's unprocessed season games. A second request inside the lock
     * window queues nothing, so refreshing or double-clicking costs nothing.
     *
     * @return array{queued: int, already_requested: bool}
     */
    public function request(int $blizzId, int $region): array
    {
        if (! Cache::add('analytics:request:'.$region.':'.$blizzId, 1, config('analytics.request_lock_seconds'))) {
            return ['queued' => 0, 'already_requested' => true];
        }

        $pending = $this->seasonGames($blizzId, $region)
            ->leftJoin('replay_deep', fn ($join) => $this->currentLoadedRow($join))
            ->whereNull('replay_deep.replayID')
            ->orderBy('replay.replayID')
            ->pluck('replay.replayID')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach (array_chunk($pending, config('analytics.fanout_chunk')) as $chunk) {
            $this->tasks->dispatchHttp(
                url: $this->fanoutUrl(),
                body: ['replay_ids' => $chunk],
                queue: config('analytics.queue'),
            );
        }

        if ($pending !== []) {
            Cache::put($this->processingKey($blizzId, $region), now()->timestamp, config('analytics.processing_seconds'));
        }

        return ['queued' => count($pending), 'already_requested' => false];
    }

    private function processingKey(int $blizzId, int $region): string
    {
        return 'analytics:processing:'.$region.':'.$blizzId;
    }

    /**
     * Run by the fan-out task: queues one analytics parse per replay, reading it from its
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

        $serviceUrl = config('analytics.service_url');
        $queued = 0;

        foreach ($replayIds as $replayId) {
            if ($archive = $archived->get($replayId)) {
                $body = [
                    'parseType' => 'deep',
                    'replayID' => $replayId,
                    'bucket' => config('analytics.archive_bucket'),
                    'input' => $archive->bundle,
                    'offset' => (int) $archive->byte_offset,
                    'length' => (int) $archive->byte_length,
                ];
            } elseif ($fingerprint = $fingerprints->get($replayId)) {
                $body = [
                    'parseType' => 'deep',
                    'replayID' => $replayId,
                    'bucket' => config('analytics.replay_bucket'),
                    'input' => $fingerprint.'.StormReplay',
                ];
            } else {
                continue;
            }

            $this->tasks->dispatchHttp(
                url: $serviceUrl,
                body: $body,
                queue: config('analytics.queue'),
                audience: $serviceUrl,
            );
            $queued++;
        }

        return $queued;
    }

    private function currentLoadedRow($join): void
    {
        $join->on('replay_deep.replayID', '=', 'replay.replayID')
            ->where('replay_deep.extract_v', '>=', config('analytics.extract_v'));
    }

    private function seasonGames(int $blizzId, int $region)
    {
        return DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->where('player.blizz_id', $blizzId)
            ->where('replay.region', $region)
            ->where('replay.game_date', '>=', config('analytics.cutover'));
    }

    // Same host the global query tasks already reach this app on, so the callback takes the
    // path Cloud Tasks is known to get through.
    private function fanoutUrl(): string
    {
        if ($url = config('analytics.fanout_url')) {
            return (string) $url;
        }

        $handler = parse_url((string) config('global.cloud_tasks.handler_url'));

        return isset($handler['scheme'], $handler['host'])
            ? $handler['scheme'].'://'.$handler['host'].(isset($handler['port']) ? ':'.$handler['port'] : '').'/api/v1/internal/analytics/fanout'
            : url('/api/v1/internal/analytics/fanout');
    }
}
