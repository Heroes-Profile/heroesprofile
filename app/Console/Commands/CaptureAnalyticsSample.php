<?php

namespace App\Console\Commands;

use App\Models\Map;
use App\Services\AnalyticsQueryService;
use App\Services\AnalyticsReplayService;
use App\Services\AnalyticsSampleService;
use App\Services\AnalyticsService;
use Illuminate\Console\Command;

/**
 * Freezes the sample account's analytics (config analytics.sample) into resources/analytics-sample
 * for the public /Analytics/Sample page: every tab for all maps and for each sample map, the
 * Activity tab's ability graphs, and one game for the replay viewer. Run it locally and commit
 * the files; the page itself never queries anything.
 */
class CaptureAnalyticsSample extends Command
{
    protected $signature = 'analytics:capture-sample
                            {--replay= : Replay ID for the replay viewer. Defaults to the latest processed game on the first sample map.}';

    protected $description = 'Write the public analytics sample from the sample account\'s games';

    // The Activity tab draws ability tables for this many of the player's heroes (AnalyticsActivity maxHeroes)
    private const ABILITY_HEROES = 5;

    public function handle(AnalyticsService $analytics, AnalyticsQueryService $queries, AnalyticsReplayService $replays, AnalyticsSampleService $sample): int
    {
        $blizzId = (int) config('analytics.sample.blizz_id');
        $region = (int) config('analytics.sample.region');
        $mapNames = config('analytics.sample.maps');
        $mapIds = Map::whereIn('name', $mapNames)->pluck('map_id', 'name');

        $missing = array_diff($mapNames, $mapIds->keys()->all());
        if ($missing !== []) {
            $this->error('Unknown sample maps: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $written = [];
        $save = function (string $file, array $data) use ($sample, &$written) {
            $sample->write($file, $data);
            $written[] = $file;
            $this->line('  '.$file);
        };

        $allMaps = null;
        foreach (array_merge([null], $mapNames) as $map) {
            $filters = ['game_types' => [], 'maps' => $map === null ? [] : [(int) $mapIds[$map]], 'hero' => 0];
            $this->info($map ?? 'All maps');

            foreach (AnalyticsQueryService::TABS as $tab) {
                $data = $queries->tab($tab, $blizzId, $region, $filters);
                $save($sample->tabFile($tab, $map), $data);

                if ($tab === 'activity') {
                    foreach ($this->abilities($data) as [$heroId, $abilityId]) {
                        $save($sample->abilityFile($heroId, $abilityId, $map), [
                            'timeline' => $queries->abilityTimeline($blizzId, $region, $filters, $heroId, $abilityId),
                        ]);
                    }
                }
            }

            $games = $replays->games($blizzId, $region, $filters);
            $save($sample->tabFile('games', $map), ['games' => $games]);
            $allMaps ??= $games;
        }

        $replayId = $this->option('replay') ? (int) $this->option('replay') : $this->pickReplay($allMaps, $mapNames[0] ?? null);
        $replay = $replayId ? $replays->replay($replayId, $blizzId) : null;
        if ($replay === null) {
            $this->warn('No processed game for the replay viewer'.($replayId ? ' (replay '.$replayId.' is not ready)' : '').'; the sample has no replay.');
            $replayId = null;
        } else {
            $replay['win_model'] = $queries->winModel();
            $save('replay', $replay);
        }

        $save('meta', [
            'captured_at' => now()->toDateString(),
            'battletag' => config('analytics.sample.battletag'),
            'status' => array_merge($analytics->status($blizzId, $region), ['processing' => false]),
            'maps' => $mapNames,
            'replay_id' => $replayId,
        ]);

        $removed = $sample->removeExcept($written);
        $this->info(count($written).' files written'.($removed ? ', '.$removed.' old ones removed' : '').'.');

        return self::SUCCESS;
    }

    /**
     * The abilities the Activity tab lists: the player's most played heroes, abilities they used.
     *
     * @return array<array{int, string}>
     */
    private function abilities(array $activity): array
    {
        $heroes = collect($activity['hero_games'] ?? [])
            ->where('who', 'you')
            ->sortByDesc('games')
            ->take(self::ABILITY_HEROES)
            ->pluck('hero_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($activity['ability_usage'] ?? [])
            ->filter(fn ($row) => $row['who'] === 'you' && in_array((int) $row['hero_id'], $heroes, true))
            ->map(fn ($row) => [(int) $row['hero_id'], (string) $row['ability_id']])
            ->unique(fn ($pair) => $pair[0].':'.$pair[1])
            ->values()
            ->all();
    }

    // The latest processed game, on the given map if there is one
    private function pickReplay(array $games, ?string $map): ?int
    {
        $processed = array_values(array_filter($games, fn ($game) => $game['processed']));
        foreach ($processed as $game) {
            if ($map !== null && data_get($game['game_map'], 'name') === $map) {
                return $game['replayID'];
            }
        }

        return $processed[0]['replayID'] ?? null;
    }
}
