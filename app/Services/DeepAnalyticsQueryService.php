<?php

namespace App\Services;

use App\Models\Battletag;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Cache;

/**
 * Reads deep analytics for one player out of BigQuery (dataset deep_analytics, filled by
 * the VM loader). Every view compares "you" (the player's processed games under the page
 * filters) with "baseline" (each player-game in the sampled games under the same filters,
 * replay_id % 1000 below the sample threshold), so baselines are never skewed toward the
 * people who use the tool.
 *
 * Results are cached per player, tab and filter set, keyed on the player's processed-game
 * count, so they refresh exactly when new games land.
 */
class DeepAnalyticsQueryService
{
    public const TABS = ['deaths', 'map', 'timelines', 'activity', 'sessions'];

    private ?BigQueryClient $client = null;

    public function __construct(private DeepAnalyticsService $deepAnalytics) {}

    /**
     * @param  array{game_types: array<int>, maps: array<int>, hero: int}  $filters
     * @return array<string, mixed>
     */
    public function tab(string $tab, int $blizzId, int $region, array $filters): array
    {
        $processed = $this->deepAnalytics->status($blizzId, $region)['done'];
        $key = 'deep_analytics:tab:v'.config('deep_analytics.extract_v').':'.$tab.':'.$region.':'.$blizzId.':'.$processed.':'.md5(json_encode($filters));

        return Cache::remember($key, now()->addDays(7), function () use ($tab, $blizzId, $region, $filters) {
            $params = [
                'blizz' => $blizzId,
                'region' => $region,
                'game_types' => array_values($filters['game_types']),
                'maps' => array_values($filters['maps']),
                'hero' => $filters['hero'],
                'cutover' => config('deep_analytics.cutover'),
                'sample' => (int) config('deep_analytics.baseline_threshold'),
            ];

            $data = $this->run($this->queries($tab), $params);

            if ($tab === 'deaths') {
                $data['nemesis_players'] = $this->withBattletags($data['nemesis_players'], $region);
                $data['victim_players'] = $this->withBattletags($data['victim_players'], $region);
            }

            if ($tab === 'deaths' || $tab === 'map') {
                $data['layout'] = $this->layout();
            }

            return $data;
        });
    }

    /**
     * Each map's structures and camps at their in-game coordinates, for drawing a schematic
     * under the map views. The same for everyone, so cached on its own.
     *
     * @return array<int, array<string, mixed>>
     */
    public function layout(): array
    {
        return Cache::remember('deep_analytics:layout', now()->addDay(), function () {
            return $this->run(['layout' => <<<'SQL'
                SELECT game_map, kind, type, team, CAST(ROUND(AVG(x)) AS INT64) AS x, CAST(ROUND(AVG(y)) AS INT64) AS y, COUNT(*) AS seen
                FROM `{dataset}.objectives`
                WHERE game_date >= @cutover AND kind IN ('town', 'building', 'camp') AND x IS NOT NULL
                GROUP BY game_map, kind, type, team, DIV(x, 3), DIV(y, 3)
                HAVING seen >= 3
                SQL], ['cutover' => config('deep_analytics.cutover')])['layout'];
        });
    }

    /**
     * Shared CTEs. `games`: the player's games under the filters. `pg`: "you" and
     * "baseline" player-games in one shape (who, replay_id, blizz, team, hero_id, winner).
     */
    private function ctes(): string
    {
        return <<<'SQL'
            WITH games AS (
              SELECT replay_id, team, hero_id, winner
              FROM `{dataset}.player_match`
              WHERE game_date >= @cutover AND blizz_id = @blizz AND region = @region
                AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR game_type IN UNNEST(@game_types))
                AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR game_map IN UNNEST(@maps))
                AND (@hero = 0 OR hero_id = @hero)
            ),
            pg AS (
              SELECT 'you' AS who, replay_id, @blizz AS blizz, team, hero_id, winner FROM games
              UNION ALL
              SELECT 'baseline', replay_id, blizz_id, team, hero_id, winner
              FROM `{dataset}.player_match`
              WHERE game_date >= @cutover AND sample_bucket < @sample
                AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR game_type IN UNNEST(@game_types))
                AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR game_map IN UNNEST(@maps))
                AND (@hero = 0 OR hero_id = @hero)
            ),
            bases AS (
              -- Where each team's heroes first appear: their own base
              SELECT hp.replay_id, hp.team, AVG(p.x) AS base_x, AVG(p.y) AS base_y
              FROM `{dataset}.hero_positions` hp, UNNEST(hp.points) p WITH OFFSET o
              WHERE hp.game_date >= @cutover AND o = 0 AND hp.replay_id IN (SELECT replay_id FROM pg)
              GROUP BY hp.replay_id, hp.team
            )
            SQL;
    }

    /** @return array<string, string> */
    private function queries(string $tab): array
    {
        $deathContext = <<<'SQL'
            death_ctx AS (
              SELECT pg.who, pg.replay_id, pg.blizz, pg.team, d.t_s, d.x, d.y, d.game_map,
                ARRAY_LENGTH(d.killer_blizz_ids) AS killers,
                SQRT(POW(d.x - eb.base_x, 2) + POW(d.y - eb.base_y, 2)) < SQRT(POW(d.x - ob.base_x, 2) + POW(d.y - ob.base_y, 2)) AS enemy_half,
                (SELECT MAX(level) FROM `{dataset}.team_levels` l WHERE l.replay_id = d.replay_id AND l.team = pg.team AND l.t_s <= d.t_s) AS own_level,
                (SELECT MAX(level) FROM `{dataset}.team_levels` l WHERE l.replay_id = d.replay_id AND l.team != pg.team AND l.t_s <= d.t_s) AS enemy_level
              FROM pg
              JOIN `{dataset}.deaths` d ON d.replay_id = pg.replay_id AND d.victim_blizz_id = pg.blizz AND d.game_date >= @cutover
              LEFT JOIN bases ob ON ob.replay_id = pg.replay_id AND ob.team = pg.team
              LEFT JOIN bases eb ON eb.replay_id = pg.replay_id AND eb.team != pg.team
            )
            SQL;

        $phase = 'CASE WHEN t_s < 300 THEN 0 WHEN t_s < 600 THEN 1 WHEN t_s < 900 THEN 2 WHEN t_s < 1200 THEN 3 ELSE 4 END';

        $levelAt = fn (string $team, string $seconds) => "(SELECT MAX(level) FROM `{dataset}.team_levels` l WHERE l.replay_id = pg.replay_id AND l.team {$team} pg.team AND l.t_s <= {$seconds})";

        return match ($tab) {
            'deaths' => [
                'death_points' => $this->ctes().", {$deathContext}
                    SELECT game_map, x, y, t_s, killers, enemy_half FROM death_ctx WHERE who = 'you' AND x IS NOT NULL LIMIT 20000",

                'death_timing' => $this->ctes().',
                    per_game AS (
                      SELECT pg.who, pg.replay_id, pg.blizz,
                        COUNTIF(d.t_s < 300) AS p0, COUNTIF(d.t_s >= 300 AND d.t_s < 600) AS p1, COUNTIF(d.t_s >= 600 AND d.t_s < 900) AS p2,
                        COUNTIF(d.t_s >= 900 AND d.t_s < 1200) AS p3, COUNTIF(d.t_s >= 1200) AS p4, MIN(d.t_s) AS first_death
                      FROM pg LEFT JOIN `{dataset}.deaths` d ON d.replay_id = pg.replay_id AND d.victim_blizz_id = pg.blizz AND d.game_date >= @cutover
                      GROUP BY pg.who, pg.replay_id, pg.blizz
                    )
                    SELECT who, COUNT(*) AS games, AVG(p0) AS p0, AVG(p1) AS p1, AVG(p2) AS p2, AVG(p3) AS p3, AVG(p4) AS p4,
                      AVG(first_death) AS avg_first_death, COUNTIF(first_death IS NULL) / COUNT(*) AS deathless_share
                    FROM per_game GROUP BY who',

                'death_context' => $this->ctes().", {$deathContext}
                    SELECT who, COUNT(*) AS deaths,
                      COUNTIF(killers = 0) AS no_hero_killer, COUNTIF(killers BETWEEN 1 AND 2) AS ganked, COUNTIF(killers >= 3) AS teamfight,
                      COUNTIF(own_level < enemy_level) AS level_behind, COUNTIF(own_level = enemy_level) AS level_even, COUNTIF(own_level > enemy_level) AS level_ahead,
                      COUNTIF(enemy_half) AS enemy_half
                    FROM death_ctx GROUP BY who",

                'overextension' => $this->ctes().", {$deathContext}
                    SELECT who, {$phase} AS phase, COUNT(*) AS deaths, COUNTIF(enemy_half) AS enemy_half
                    FROM death_ctx GROUP BY who, phase ORDER BY who, phase",

                'nemesis_heroes' => $this->ctes().'
                    SELECT k AS hero_id, COUNT(*) AS n
                    FROM `{dataset}.deaths` d JOIN games g ON g.replay_id = d.replay_id, UNNEST(d.killer_hero_ids) k
                    WHERE d.game_date >= @cutover AND d.victim_blizz_id = @blizz
                    GROUP BY k ORDER BY n DESC LIMIT 15',

                'nemesis_players' => $this->ctes().'
                    SELECT k AS blizz_id, COUNT(*) AS n
                    FROM `{dataset}.deaths` d JOIN games g ON g.replay_id = d.replay_id, UNNEST(d.killer_blizz_ids) k
                    WHERE d.game_date >= @cutover AND d.victim_blizz_id = @blizz
                    GROUP BY k ORDER BY n DESC LIMIT 10',

                'victim_heroes' => $this->ctes().'
                    SELECT d.victim_hero_id AS hero_id, COUNT(*) AS n
                    FROM `{dataset}.deaths` d JOIN games g ON g.replay_id = d.replay_id
                    WHERE d.game_date >= @cutover AND @blizz IN UNNEST(d.killer_blizz_ids)
                    GROUP BY hero_id ORDER BY n DESC LIMIT 15',

                'victim_players' => $this->ctes().'
                    SELECT d.victim_blizz_id AS blizz_id, COUNT(*) AS n
                    FROM `{dataset}.deaths` d JOIN games g ON g.replay_id = d.replay_id
                    WHERE d.game_date >= @cutover AND @blizz IN UNNEST(d.killer_blizz_ids)
                    GROUP BY blizz_id ORDER BY n DESC LIMIT 10',
            ],

            'map' => [
                'heatmap' => $this->ctes().'
                    SELECT hp.game_map, hp.team, p.est, DIV(p.x, 4) * 4 AS x, DIV(p.y, 4) * 4 AS y, COUNT(*) AS n
                    FROM `{dataset}.hero_positions` hp JOIN games g ON g.replay_id = hp.replay_id, UNNEST(hp.points) p
                    WHERE hp.game_date >= @cutover AND hp.blizz_id = @blizz AND p.est IS NOT NULL
                    GROUP BY hp.game_map, hp.team, p.est, x, y',
            ],

            'timelines' => [
                'level_lead' => $this->ctes().",
                    minutes AS (SELECT m FROM UNNEST(GENERATE_ARRAY(0, 40)) m),
                    per_minute AS (
                      SELECT pg.who, pg.winner, minutes.m,
                        {$levelAt('=', 'minutes.m * 60')} AS own, {$levelAt('!=', 'minutes.m * 60')} AS enemy
                      FROM pg CROSS JOIN minutes JOIN `{dataset}.match` mt ON mt.replay_id = pg.replay_id AND mt.game_date >= @cutover
                      WHERE minutes.m * 60 <= mt.length_s
                    )
                    SELECT who, m AS minute, AVG(own - enemy) AS lead, AVG(IF(winner, own - enemy, NULL)) AS lead_won,
                      AVG(IF(NOT winner, own - enemy, NULL)) AS lead_lost, COUNT(*) AS games
                    FROM per_minute GROUP BY who, m ORDER BY who, m",

                'comebacks' => $this->ctes().",
                    checkpoints AS (SELECT c FROM UNNEST([10, 15, 20]) c),
                    per_point AS (
                      SELECT pg.who, pg.winner, checkpoints.c,
                        {$levelAt('=', 'checkpoints.c * 60')} AS own, {$levelAt('!=', 'checkpoints.c * 60')} AS enemy
                      FROM pg CROSS JOIN checkpoints JOIN `{dataset}.match` mt ON mt.replay_id = pg.replay_id AND mt.game_date >= @cutover
                      WHERE checkpoints.c * 60 <= mt.length_s
                    )
                    SELECT who, c AS minute,
                      CASE WHEN own < enemy THEN 'behind' WHEN own > enemy THEN 'ahead' ELSE 'even' END AS state,
                      COUNT(*) AS games, AVG(IF(winner, 1, 0)) AS win_rate
                    FROM per_point GROUP BY who, minute, state ORDER BY who, minute, state",

                'tier_windows' => $this->ctes().',
                    reach AS (
                      SELECT replay_id, team, MIN(IF(level >= 10, t_s, NULL)) AS l10, MIN(IF(level >= 20, t_s, NULL)) AS l20
                      FROM `{dataset}.team_levels` WHERE game_date >= @cutover GROUP BY replay_id, team
                    ),
                    per_game AS (
                      SELECT pg.who, pg.winner, o.l10 AS own10, e.l10 AS enemy10, o.l20 AS own20, e.l20 AS enemy20, mt.length_s
                      FROM pg JOIN `{dataset}.match` mt ON mt.replay_id = pg.replay_id AND mt.game_date >= @cutover
                      LEFT JOIN reach o ON o.replay_id = pg.replay_id AND o.team = pg.team
                      LEFT JOIN reach e ON e.replay_id = pg.replay_id AND e.team != pg.team
                    )
                    SELECT who, COUNT(*) AS games,
                      COUNTIF(own10 < IFNULL(enemy10, length_s + 1)) AS first_to_10, AVG(IF(own10 < IFNULL(enemy10, length_s + 1), IF(winner, 1, 0), NULL)) AS win_rate_first_to_10,
                      COUNTIF(own20 < IFNULL(enemy20, length_s + 1)) AS first_to_20, AVG(IF(own20 < IFNULL(enemy20, length_s + 1), IF(winner, 1, 0), NULL)) AS win_rate_first_to_20,
                      AVG(IF(own10 IS NOT NULL, LEAST(IFNULL(enemy10, length_s), length_s) - own10, NULL)) AS avg_seconds_ahead_10,
                      AVG(IF(own20 IS NOT NULL, LEAST(IFNULL(enemy20, length_s), length_s) - own20, NULL)) AS avg_seconds_ahead_20
                    FROM per_game GROUP BY who',

                'objective_timing' => $this->ctes().",
                    per_game AS (
                      SELECT pg.who, pg.winner,
                        MIN(IF(o.kind = 'objective' AND o.team = pg.team, o.t_s, NULL)) AS own_objective,
                        MIN(IF(o.kind = 'objective' AND o.team != pg.team, o.t_s, NULL)) AS enemy_objective,
                        MIN(IF(o.kind = 'camp' AND o.team = pg.team, o.t_s, NULL)) AS own_camp,
                        MIN(IF(o.kind = 'camp' AND o.team != pg.team, o.t_s, NULL)) AS enemy_camp,
                        MIN(IF(o.kind = 'town' AND o.team != pg.team, o.t_s, NULL)) AS own_first_fort,
                        MIN(IF(o.kind = 'town' AND o.team = pg.team, o.t_s, NULL)) AS enemy_first_fort
                      FROM pg LEFT JOIN `{dataset}.objectives` o ON o.replay_id = pg.replay_id AND o.game_date >= @cutover
                      GROUP BY pg.who, pg.replay_id, pg.blizz, pg.team, pg.winner
                    )
                    SELECT who, COUNT(*) AS games,
                      AVG(own_objective) AS avg_first_objective, AVG(own_camp) AS avg_first_camp, AVG(own_first_fort) AS avg_first_fort,
                      AVG(IF(own_objective < IFNULL(enemy_objective, 99999), IF(winner, 1, 0), NULL)) AS win_rate_first_objective,
                      AVG(IF(own_camp < IFNULL(enemy_camp, 99999), IF(winner, 1, 0), NULL)) AS win_rate_first_camp,
                      AVG(IF(own_first_fort < IFNULL(enemy_first_fort, 99999), IF(winner, 1, 0), NULL)) AS win_rate_first_fort
                    FROM per_game GROUP BY who",

                'xp_sources' => $this->ctes().',
                    final AS (
                      SELECT x.replay_id, x.team, x.minion, x.creep, x.structure, x.hero, x.trickle
                      FROM `{dataset}.xp_breakdown` x
                      WHERE x.game_date >= @cutover
                      QUALIFY ROW_NUMBER() OVER (PARTITION BY x.replay_id, x.team ORDER BY x.t_s DESC) = 1
                    )
                    SELECT pg.who, pg.winner, COUNT(*) AS games,
                      AVG(f.minion) AS minion, AVG(f.creep) AS creep, AVG(f.structure) AS structure, AVG(f.hero) AS hero, AVG(f.trickle) AS trickle
                    FROM pg JOIN final f ON f.replay_id = pg.replay_id AND f.team = pg.team
                    GROUP BY pg.who, pg.winner',

                'takedowns' => $this->ctes().',
                    per_game AS (
                      SELECT pg.who, COUNT(d.replay_id) AS enemy_deaths,
                        COUNTIF(pg.blizz IN UNNEST(d.killer_blizz_ids)) AS participated,
                        COUNTIF(ARRAY_LENGTH(d.killer_blizz_ids) = 1 AND d.killer_blizz_ids[OFFSET(0)] = pg.blizz) AS solo
                      FROM pg LEFT JOIN `{dataset}.deaths` d ON d.replay_id = pg.replay_id AND d.victim_team != pg.team AND d.game_date >= @cutover
                      GROUP BY pg.who, pg.replay_id, pg.blizz
                    )
                    SELECT who, COUNT(*) AS games, AVG(enemy_deaths) AS enemy_deaths, AVG(participated) AS takedowns,
                      SAFE_DIVIDE(SUM(participated), SUM(enemy_deaths)) AS participation, AVG(solo) AS solo_kills
                    FROM per_game GROUP BY who',

                'takedown_phases' => $this->ctes().",
                    td AS (
                      SELECT pg.who, d.t_s, pg.blizz IN UNNEST(d.killer_blizz_ids) AS participated
                      FROM pg JOIN `{dataset}.deaths` d ON d.replay_id = pg.replay_id AND d.victim_team != pg.team AND d.game_date >= @cutover
                    )
                    SELECT who, {$phase} AS phase, COUNT(*) AS enemy_deaths, COUNTIF(participated) AS takedowns
                    FROM td GROUP BY who, phase ORDER BY who, phase",
            ],

            'activity' => [
                'apm' => $this->ctes().'
                    SELECT pg.who, minute, AVG(c) AS commands, COUNT(*) AS games
                    FROM pg JOIN `{dataset}.activity` a ON a.replay_id = pg.replay_id AND a.blizz_id = pg.blizz AND a.game_date >= @cutover,
                      UNNEST(a.cmds_per_min) c WITH OFFSET minute
                    WHERE minute < 40
                    GROUP BY pg.who, minute ORDER BY pg.who, minute',

                'talent_timing' => $this->ctes().'
                    SELECT pg.who, tp.tier, AVG(tp.t_s) AS avg_seconds, COUNT(*) AS picks
                    FROM pg JOIN `{dataset}.talent_picks` tp ON tp.replay_id = pg.replay_id AND tp.blizz_id = pg.blizz AND tp.game_date >= @cutover
                    GROUP BY pg.who, tp.tier ORDER BY pg.who, tp.tier',
            ],

            'sessions' => [
                'games' => $this->ctes().'
                    SELECT g.replay_id, FORMAT_DATETIME("%Y-%m-%dT%H:%M:%S", m.game_date) AS game_date, m.length_s, m.game_map, g.hero_id, g.winner
                    FROM games g JOIN `{dataset}.match` m ON m.replay_id = g.replay_id AND m.game_date >= @cutover
                    ORDER BY m.game_date',

                'disconnects' => $this->ctes().",
                    leaves AS (
                      -- A leave in the last 30 s is the end of the game, not a disconnect
                      SELECT dc.replay_id, p.team
                      FROM `{dataset}.disconnects` dc
                      JOIN `{dataset}.player_match` p ON p.replay_id = dc.replay_id AND p.blizz_id = dc.blizz_id AND p.game_date >= @cutover
                      JOIN `{dataset}.match` m ON m.replay_id = dc.replay_id AND m.game_date >= @cutover
                      WHERE dc.game_date >= @cutover AND dc.kind = 'leave' AND dc.t_s < m.length_s - 30
                      GROUP BY dc.replay_id, p.team
                    ),
                    per_game AS (
                      SELECT g.winner,
                        EXISTS(SELECT 1 FROM leaves l WHERE l.replay_id = g.replay_id AND l.team = g.team) AS own_dc,
                        EXISTS(SELECT 1 FROM leaves l WHERE l.replay_id = g.replay_id AND l.team != g.team) AS enemy_dc
                      FROM games g
                    )
                    SELECT COUNT(*) AS games,
                      COUNTIF(own_dc) AS own_dc_games, AVG(IF(own_dc, IF(winner, 1, 0), NULL)) AS win_rate_own_dc,
                      COUNTIF(enemy_dc) AS enemy_dc_games, AVG(IF(enemy_dc, IF(winner, 1, 0), NULL)) AS win_rate_enemy_dc,
                      AVG(IF(NOT own_dc AND NOT enemy_dc, IF(winner, 1, 0), NULL)) AS win_rate_no_dc
                    FROM per_game",
            ],
        };
    }

    /**
     * Starts every query at once so BigQuery runs them in parallel, then collects them.
     *
     * @param  array<string, string>  $queries
     * @param  array<string, mixed>  $params
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function run(array $queries, array $params): array
    {
        $client = $this->client ??= new BigQueryClient(['projectId' => config('deep_analytics.bigquery_project')]);
        $dataset = config('deep_analytics.bigquery_project').'.'.config('deep_analytics.bigquery_dataset');

        // Element types for the array params, so an empty filter list is still ARRAY<INT64>
        $types = array_intersect_key(['game_types' => 'INT64', 'maps' => 'INT64'], $params);

        $jobs = [];
        foreach ($queries as $name => $sql) {
            $config = $client->query(str_replace('{dataset}', $dataset, $sql))
                ->parameters($params)
                ->setParamTypes($types)
                ->location(config('deep_analytics.bigquery_location'));
            $jobs[$name] = $client->startQuery($config);
        }

        $results = [];
        foreach ($jobs as $name => $job) {
            $rows = [];
            foreach ($job->queryResults() as $row) {
                $rows[] = array_map(fn ($value) => is_object($value) ? (string) $value : $value, $row);
            }
            $results[$name] = $rows;
        }

        return $results;
    }

    /**
     * Adds each player's current battletag, unless they have opted out.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function withBattletags(array $rows, int $region): array
    {
        $ids = array_map(fn ($row) => (int) $row['blizz_id'], $rows);
        if ($ids === []) {
            return $rows;
        }

        $tags = Battletag::whereIn('blizz_id', $ids)
            ->where('region', $region)
            ->where(fn ($q) => $q->whereNull('opt_out')->orWhere('opt_out', 0))
            ->orderBy('latest_game')
            ->get(['blizz_id', 'battletag'])
            ->pluck('battletag', 'blizz_id');

        return array_map(fn ($row) => $row + ['battletag' => $tags[(int) $row['blizz_id']] ?? null], $rows);
    }
}
