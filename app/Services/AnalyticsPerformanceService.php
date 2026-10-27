<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Match performance: how each player in one processed game played, metric by metric, against the
 * same hero on the same map in the global sample.
 *
 * A player's metrics (BigQuery player_game_metrics, run for this one replay) are rated against games
 * with the same result: a loss against losses, a win against wins, so a good game on a losing team
 * still rates well. Each metric also carries the winners' typical range, to show how far it is from a
 * winning game. Ratings come from the nightly performance_baseline and performance_weights tables.
 */
class AnalyticsPerformanceService
{
    // Fewest sample games behind a baseline; below it the hero's games on every map are used instead
    private const MIN_GAMES = 30;

    // A metric counts toward ratings only on heroes where it goes with winning at least this much
    private const MIN_WEIGHT = 0.05;

    private const MIN_WEIGHT_GAMES = 100;

    public const AREAS = ['combat', 'survival', 'teamfights', 'objectives', 'positioning', 'abilities', 'movement', 'support'];

    public function __construct(
        private AnalyticsQueryService $queries,
        private AnalyticsReplayService $replays,
    ) {}

    /**
     * Every player's area ratings, overall rating and metric breakdown, keyed by the viewer's player
     * index. Null when the game isn't processed or has no metrics.
     *
     * @return array<string, mixed>|null
     */
    public function forReplay(int $replayId): ?array
    {
        if (! $this->replays->isReady($replayId)) {
            return null;
        }

        $key = 'analytics:performance:v1:'.config('analytics.extract_v').':'.$replayId.':'.$this->baselineVersion();

        return Cache::remember($key, now()->addDays(7), fn () => $this->build($replayId));
    }

    /** @return array<string, mixed>|null */
    private function build(int $replayId): ?array
    {
        $replay = DB::connection('heroesprofile')->table('replay')->where('replayID', $replayId)->first(['game_date', 'game_map', 'game_version']);
        $indexes = $this->replays->playerIndexes($replayId);
        if (! $replay || $indexes === []) {
            return null;
        }
        $heroes = DB::connection('heroesprofile')->table('player')->where('replayID', $replayId)->pluck('hero')->map(fn ($hero) => (int) $hero)->unique()->values()->all();

        $data = $this->queries->run([
            'metrics' => 'SELECT blizz_id, hero_id, win, area, metric, value
                FROM `{dataset}.player_game_metrics`([@replay], DATETIME(@game_date), DATETIME(@game_date))',
            'baseline' => 'SELECT hero_id, game_map, won, metric, games, q, CAST(computed_at AS STRING) AS computed_at
                FROM `{dataset}.performance_baseline`
                WHERE hero_id IN UNNEST(@heroes) AND (game_map = @map OR game_map IS NULL)',
            'weights' => 'SELECT hero_id, metric, games, r_win FROM `{dataset}.performance_weights` WHERE hero_id IN UNNEST(@heroes)',
        ], [
            'replay' => $replayId,
            'game_date' => (string) $replay->game_date,
            'map' => (int) $replay->game_map,
            'heroes' => $heroes,
        ]);
        if ($data['metrics'] === []) {
            return null;
        }

        $build = explode('.', (string) $replay->game_version)[3] ?? null;
        $abilities = $this->queries->abilityNames(
            $build === null ? null : (int) $build,
            array_values(array_unique(array_filter(array_map(
                fn ($row) => str_starts_with($row['metric'], 'ability:') ? (int) substr($row['metric'], 8) : null,
                $data['metrics']
            ))))
        );

        return $this->rate($data, $indexes, $abilities);
    }

    /**
     * Rates each player's metrics from the BigQuery results.
     *
     * @param  array<string, array<int, array<string, mixed>>>  $data  metrics, baseline and weights rows
     * @param  array<int, int>  $indexes  viewer player index by blizz ID
     * @param  array<int, array<string, mixed>>  $abilities  ability names by link
     * @return array<string, mixed>
     */
    private function rate(array $data, array $indexes, array $abilities): array
    {
        $baselines = [];
        foreach ($data['baseline'] as $row) {
            $scope = $row['game_map'] === null ? 'all_maps' : 'map';
            $baselines[$row['hero_id'].':'.$row['metric'].':'.($row['won'] ? 1 : 0).':'.$scope] = [
                'games' => (int) $row['games'],
                'q' => array_map('floatval', $row['q']),
            ];
        }
        $weights = [];
        foreach ($data['weights'] as $row) {
            if ($row['r_win'] !== null && (int) $row['games'] >= self::MIN_WEIGHT_GAMES) {
                $weights[$row['hero_id'].':'.$row['metric']] = (float) $row['r_win'];
            }
        }

        $players = [];
        foreach ($data['metrics'] as $row) {
            $blizz = (int) $row['blizz_id'];
            if (! isset($indexes[$blizz])) {
                continue;
            }
            $hero = (int) $row['hero_id'];
            $won = (int) $row['win'] === 1;
            $metric = $row['metric'];
            $value = (float) $row['value'];

            $same = $this->baseline($baselines, $hero, $metric, $won);
            $winners = $this->baseline($baselines, $hero, $metric, true);
            $weight = $weights[$hero.':'.$metric] ?? null;
            $rated = $same !== null && $weight !== null && abs($weight) >= self::MIN_WEIGHT;
            $percentile = $same === null ? null : $this->percentile($value, $same['q']);

            $entry = [
                'metric' => $metric,
                'value' => round($value, 4),
                // Where the value sits among same-result games, 0-100
                'percentile' => $percentile === null ? null : round($percentile, 1),
                // The same, turned so 100 is best: flipped when less goes with winning
                'rating' => $rated ? round($weight > 0 ? $percentile : 100 - $percentile, 1) : null,
                'better' => $weight === null || abs($weight) < self::MIN_WEIGHT ? null : ($weight > 0 ? 'higher' : 'lower'),
                'weight' => $weight === null ? null : round(abs($weight), 3),
                'scope' => $same['scope'] ?? null,
                'games' => $same['games'] ?? null,
                'winners' => $winners === null ? null : [
                    'p25' => round($winners['q'][5], 4),
                    'p50' => round($winners['q'][10], 4),
                    'p75' => round($winners['q'][15], 4),
                    'scope' => $winners['scope'],
                    'games' => $winners['games'],
                ],
            ];
            if (str_starts_with($metric, 'ability:')) {
                $ability = $abilities[(int) substr($metric, 8)] ?? null;
                if ($ability === null) {
                    continue;
                }
                $entry += $ability;
            }

            $players[$blizz] ??= ['idx' => $indexes[$blizz], 'hero_id' => $hero, 'won' => $won, 'areas' => []];
            $players[$blizz]['areas'][$row['area']]['metrics'][] = $entry;
        }

        foreach ($players as &$player) {
            $ratings = [];
            foreach ($player['areas'] as $area => &$block) {
                $block['rating'] = $this->areaRating($block['metrics']);
                if ($block['rating'] !== null) {
                    $ratings[] = $block['rating'];
                }
                // Biggest gaps from the typical game first
                usort($block['metrics'], fn ($a, $b) => ($a['rating'] ?? 50) <=> ($b['rating'] ?? 50));
            }
            unset($block);
            $player['overall'] = $ratings === [] ? null : round(array_sum($ratings) / count($ratings), 1);
        }
        unset($player);

        return [
            'computed_at' => $data['baseline'][0]['computed_at'] ?? null,
            'min_games' => self::MIN_GAMES,
            'players' => array_values($players),
        ];
    }

    /**
     * The hero's baseline for a metric and result on this map, or across all its maps when the map
     * has too few games. Null when neither has enough.
     *
     * @return array{games: int, q: array<int, float>, scope: string}|null
     */
    private function baseline(array $baselines, int $hero, string $metric, bool $won): ?array
    {
        foreach (['map', 'all_maps'] as $scope) {
            $row = $baselines[$hero.':'.$metric.':'.($won ? 1 : 0).':'.$scope] ?? null;
            if ($row !== null && $row['games'] >= self::MIN_GAMES) {
                return $row + ['scope' => $scope];
            }
        }

        return null;
    }

    /**
     * Where a value sits in a baseline's 5% steps, 0-100. A value equal to several steps (often 0)
     * sits in the middle of them.
     *
     * @param  array<int, float>  $q
     */
    private function percentile(float $value, array $q): float
    {
        $last = count($q) - 1;
        $step = 100 / $last;

        $equal = array_keys(array_filter($q, fn ($v) => abs($v - $value) < 1e-9));
        if ($equal !== []) {
            return array_sum($equal) / count($equal) * $step;
        }
        if ($value < $q[0]) {
            return 0;
        }
        if ($value > $q[$last]) {
            return 100;
        }
        for ($i = 0; $i < $last; $i++) {
            if ($value > $q[$i] && $value < $q[$i + 1]) {
                return ($i + ($value - $q[$i]) / ($q[$i + 1] - $q[$i])) * $step;
            }
        }

        return 50;
    }

    /**
     * An area's rating: its rated metrics, each weighted by how strongly it goes with winning on the hero.
     *
     * @param  array<int, array<string, mixed>>  $metrics
     */
    private function areaRating(array $metrics): ?float
    {
        $sum = 0;
        $weights = 0;
        foreach ($metrics as $metric) {
            if ($metric['rating'] !== null) {
                $sum += $metric['rating'] * $metric['weight'];
                $weights += $metric['weight'];
            }
        }

        return $weights > 0 ? round($sum / $weights, 1) : null;
    }

    /** When the baselines were last rebuilt, checked every 10 minutes. */
    private function baselineVersion(): string
    {
        return Cache::remember('analytics:performance_version', now()->addMinutes(10), function () {
            $rows = $this->queries->run(['v' => 'SELECT CAST(MAX(computed_at) AS STRING) AS computed_at FROM `{dataset}.performance_baseline`'], []);

            return (string) ($rows['v'][0]['computed_at'] ?? 'none');
        });
    }
}
