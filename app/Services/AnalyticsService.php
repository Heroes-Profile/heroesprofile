<?php

namespace App\Services;

use App\Models\Api\ApiAccount;
use App\Services\Api\PlanService;
use Carbon\CarbonInterface;
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

    /**
     * Who can use the Analytics pages (not the replay viewer), and why: the allowlist, a current
     * Patreon supporter, a paid plan on a linked API account, or a free trial still running. A supporter is a linked Patreon account
     * with site flair or ad-free on, the same test as Patreon flair elsewhere; both flags drop when
     * a pledge lapses. Each development stage (config analytics.trial_stage) has its own trial;
     * trial_available is true until the account starts the current stage's (a running one from an
     * earlier stage still counts until it ends).
     *
     * @return array{allowed: bool, via: ?string, trial_available: bool, trial_ends_at: ?CarbonInterface, trial_stage: string}
     */
    public function access($user): array
    {
        $stage = (string) config('analytics.trial_stage');
        $thisStage = $user->analytics_trial_started_at !== null && $user->analytics_trial_stage === $stage;
        // A trial still running when the stage changes runs to its end; the new stage's starts after
        $trialEnds = $user->analytics_trial_started_at?->copy()->addDays((int) config('analytics.trial_days'));

        $via = match (true) {
            in_array((int) $user->blizz_id, config('analytics.allowlist'), true) => 'allowlist',
            $this->isSupporter($user) => 'patreon',
            $this->hasPaidApiPlan($user) => 'api',
            $trialEnds !== null && $trialEnds->isFuture() => 'trial',
            default => null,
        };

        return [
            'allowed' => $via !== null,
            'via' => $via,
            'trial_available' => ! $thisStage,
            'trial_ends_at' => $trialEnds,
            'trial_stage' => $stage,
        ];
    }

    public function isAllowed($user): bool
    {
        return $this->access($user)['allowed'];
    }

    /**
     * Starts the account's free trial for the current stage. Nothing happens if it already started
     * this stage's, or already has access another way (a trial then would only be used up for nothing).
     */
    public function startTrial($user): bool
    {
        $access = $this->access($user);
        if (! $access['trial_available'] || $access['allowed']) {
            return false;
        }

        $user->forceFill(['analytics_trial_started_at' => now(), 'analytics_trial_stage' => $access['trial_stage']])->save();

        return true;
    }

    /**
     * The free processing allowance for an account without a subscription (trial or not): used,
     * limit and left for the current stage. Null when it has Patreon, a paid API plan or the allowlist.
     *
     * @return array{used: int, limit: int, left: int}|null
     */
    public function freeGames($user, ?array $access = null): ?array
    {
        $access ??= $this->access($user);
        if (in_array($access['via'], ['allowlist', 'patreon', 'api'], true)) {
            return null;
        }

        $limit = (int) config('analytics.free_games');
        $used = $user->analytics_free_stage === $access['trial_stage'] ? (int) $user->analytics_free_games : 0;

        return ['used' => $used, 'limit' => $limit, 'left' => max(0, $limit - $used)];
    }

    public function useFreeGames($user, int $games): void
    {
        if ($games <= 0) {
            return;
        }

        $stage = (string) config('analytics.trial_stage');
        if ($user->analytics_free_stage === $stage) {
            $user->increment('analytics_free_games', $games);
        } else {
            $user->forceFill(['analytics_free_games' => $games, 'analytics_free_stage' => $stage])->save();
        }
    }

    /**
     * A paid plan on an API account that unlocks this Battle.net account: the one linked to it in
     * the API portal's Player Analytics section, or failing that, one whose Twitch extension setup
     * connected it (only while that account has no Player Analytics link of its own).
     */
    private function hasPaidApiPlan($user): bool
    {
        $blizzId = (int) $user->blizz_id;
        $region = (int) $user->region;

        $accounts = ApiAccount::where('analytics_blizz_id', $blizzId)->where('analytics_region', $region)->get();
        if ($accounts->isEmpty()) {
            $accounts = ApiAccount::whereNull('analytics_blizz_id')
                ->whereHas('twitchChannel', fn ($q) => $q->where('blizz_id', $blizzId)->where('region', $region))
                ->get();
        }

        $plans = app(PlanService::class);

        return $accounts->contains(fn (ApiAccount $account) => $plans->holdsPaidPlan($account));
    }

    private function isSupporter($user): bool
    {
        $patreon = $user->patreonAccount;

        return $patreon && ($patreon->site_flair == 1 || $patreon->ad_free == 1);
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

        // The done count the last request is working towards (fewer than all games when capped)
        $target = Cache::get($this->processingKey($blizzId, $region));
        $processing = $target !== null;
        if ($processing && $done >= min($total, (int) $target)) {
            Cache::forget($this->processingKey($blizzId, $region));
            $processing = false;
        }

        return ['total' => $total, 'done' => $done, 'processing' => $processing];
    }

    /**
     * Queues the player's unprocessed season games, or with a limit only that many of the newest. A
     * second request inside the lock window queues nothing, so refreshing or double-clicking costs nothing.
     *
     * @return array{queued: int, already_requested: bool}
     */
    public function request(int $blizzId, int $region, ?int $limit = null): array
    {
        if (! Cache::add('analytics:request:'.$region.':'.$blizzId, 1, config('analytics.request_lock_seconds'))) {
            return ['queued' => 0, 'already_requested' => true];
        }

        $pending = $this->seasonGames($blizzId, $region)
            ->leftJoin('replay_deep', fn ($join) => $this->currentLoadedRow($join))
            ->whereNull('replay_deep.replayID')
            ->orderByDesc('replay.replayID')
            ->when($limit !== null, fn ($query) => $query->limit($limit))
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
            $target = $this->status($blizzId, $region)['done'] + count($pending);
            Cache::put($this->processingKey($blizzId, $region), $target, config('analytics.processing_seconds'));
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
