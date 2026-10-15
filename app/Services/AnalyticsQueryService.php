<?php

namespace App\Services;

use App\Models\AbilityLink;
use App\Models\Battletag;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Reads analytics for one player out of BigQuery (dataset deep_analytics, filled by
 * the VM loader). Every view compares "you" (the player's processed games under the page
 * filters) with "global" (each player-game in the sampled games under the same filters,
 * replay_id % 1000 below the sample threshold), so global stats are never skewed toward the
 * people who use the tool.
 *
 * Results are cached per player, tab and filter set, keyed on the player's processed-game
 * count, so they refresh exactly when new games land.
 */
class AnalyticsQueryService
{
    public const TABS = ['deaths', 'map', 'timelines', 'activity', 'sessions', 'coaching', 'stacks'];

    private ?BigQueryClient $client = null;

    public function __construct(private AnalyticsService $analytics) {}

    /**
     * @param  array{game_types: array<int>, maps: array<int>, hero: int}  $filters
     * @return array<string, mixed>
     */
    public function tab(string $tab, int $blizzId, int $region, array $filters): array
    {
        $processed = $this->analytics->status($blizzId, $region)['done'];
        $threshold = $this->globalThreshold();
        // Coaching tables are rebuilt by a nightly BigQuery job (or by hand); refresh whenever they change
        $nightly = $tab === 'coaching' ? ':'.$this->coachingVersion() : '';
        $key = 'analytics:tab:v'.config('analytics.extract_v').'.8:'.$tab.':'.$region.':'.$blizzId.':'.$processed.':'.$threshold.$nightly.':'.md5(json_encode($filters));

        return Cache::remember($key, now()->addDays(7), function () use ($tab, $blizzId, $region, $filters, $threshold) {
            $params = [
                'blizz' => $blizzId,
                'region' => $region,
                'game_types' => array_values($filters['game_types']),
                'maps' => array_values($filters['maps']),
                'hero' => $filters['hero'],
                'cutover' => config('analytics.cutover'),
                'sample' => $threshold,
            ];

            $data = $this->run($this->queries($tab), $params);

            if ($tab === 'deaths') {
                $data['nemesis_players'] = $this->withBattletags($data['nemesis_players'], $region);
                $data['victim_players'] = $this->withBattletags($data['victim_players'], $region);
            }

            if ($tab === 'deaths' || $tab === 'map') {
                $data['layout'] = $this->layout();
            }

            if ($tab === 'activity') {
                $data['ability_usage'] = $this->namedAbilities($data['ability_usage'], $data['hero_games']);
            }

            return $data;
        });
    }

    /** When the coaching tables were last rebuilt, checked every 10 minutes. */
    private function coachingVersion(): string
    {
        return Cache::remember('analytics:coaching_version', now()->addMinutes(10), function () {
            $rows = $this->run(['v' => 'SELECT CAST(MAX(computed_at) AS STRING) AS computed_at FROM `{dataset}.coaching_signal_stats`'], []);

            return (string) ($rows['v'][0]['computed_at'] ?? 'none');
        });
    }

    /**
     * Global stats use replays with replayID % 1000 below this. It follows the nightly sampler's
     * own setting (processing_check.sample_rate for DeepAnalyticsGlobal, a percent), so the site
     * only counts buckets the sampler processes. Falls back to the config value if the row
     * can't be read.
     */
    public function globalThreshold(): int
    {
        return Cache::remember('analytics:global_threshold', now()->addMinutes(5), function () {
            try {
                $rate = DB::connection('heroesprofile_processing')->table('processing_check')
                    ->where('process', 'DeepAnalyticsGlobal')
                    ->value('sample_rate');
            } catch (\Throwable $e) {
                Log::warning('Analytics: could not read the global sample rate, using the config fallback', ['error' => $e->getMessage()]);
                $rate = null;
            }

            if ($rate === null || $rate < 0 || $rate > 100) {
                return (int) config('analytics.global_threshold');
            }

            return (int) round($rate * 10);
        });
    }

    /**
     * One ability's presses and casts per game minute (you vs global, same hero), with your
     * deaths and takedowns per minute to lay over it. Averages divide by the games still
     * running at that minute.
     *
     * @param  array{game_types: array<int>, maps: array<int>, hero: int}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function abilityTimeline(int $blizzId, int $region, array $filters, int $heroId, string $abilityId): array
    {
        $links = AbilityLink::where('ability_id', $abilityId)
            ->where(fn ($q) => $q->where('hero_id', $heroId)->orWhereNull('hero_id'))
            ->get(['build', 'abil_link']);
        if ($links->isEmpty()) {
            return [];
        }

        $processed = $this->analytics->status($blizzId, $region)['done'];
        $threshold = $this->globalThreshold();
        $key = 'analytics:ability:v'.config('analytics.extract_v').'.3:'.$region.':'.$blizzId.':'.$processed.':'.$threshold.':'.$heroId.':'.$abilityId.':'.md5(json_encode($filters));

        return Cache::remember($key, now()->addDays(7), function () use ($blizzId, $region, $filters, $heroId, $links, $threshold) {
            return $this->run(['timeline' => $this->ctes().',
                hero_pg AS (
                  SELECT pg.who, pg.replay_id, pg.blizz, m.length_s,
                    SAFE_CAST(SPLIT(m.game_version, ".")[SAFE_OFFSET(3)] AS INT64) AS build
                  FROM pg JOIN `{dataset}.match` m ON m.replay_id = pg.replay_id AND m.game_date >= @cutover
                  WHERE pg.hero_id = @ability_hero
                ),
                mapped AS (
                  -- Each game reads the mapping of its build, or the newest mapped build before it
                  SELECT *, IFNULL((SELECT MAX(b) FROM UNNEST(@builds) b WHERE b <= build), (SELECT MIN(b) FROM UNNEST(@builds) b)) AS mapped_build
                  FROM hero_pg
                ),
                per_game AS (
                  -- Some abilities send two command indexes per press; the busiest one stands for the ability
                  SELECT h.who, h.replay_id,
                    ARRAY_AGG(STRUCT(u.presses_per_min AS presses, u.casts_per_min AS casts) ORDER BY u.uses DESC LIMIT 1)[OFFSET(0)] AS top
                  FROM mapped h
                  JOIN `{dataset}.ability_usage` u ON u.replay_id = h.replay_id AND u.blizz_id = h.blizz AND u.game_date >= @cutover
                  WHERE CONCAT(CAST(h.mapped_build AS STRING), ":", CAST(u.abil_link AS STRING)) IN UNNEST(@keys)
                  GROUP BY h.who, h.replay_id
                ),
                ability_minutes AS (
                  SELECT who, i AS minute, SUM(top.presses[SAFE_OFFSET(i)]) AS presses, SUM(top.casts[SAFE_OFFSET(i)]) AS casts
                  FROM per_game, UNNEST(GENERATE_ARRAY(0, ARRAY_LENGTH(top.presses) - 1)) i
                  GROUP BY who, minute
                ),
                alive AS (
                  SELECT who, minute, COUNT(*) AS games
                  FROM mapped, UNNEST(GENERATE_ARRAY(0, 39)) minute
                  WHERE minute * 60 < length_s
                  GROUP BY who, minute
                ),
                death_minutes AS (
                  SELECT h.who, DIV(d.t_s, 60) AS minute, COUNT(*) AS n
                  FROM mapped h JOIN `{dataset}.deaths` d ON d.replay_id = h.replay_id AND d.victim_blizz_id = h.blizz AND d.game_date >= @cutover
                  GROUP BY who, minute
                ),
                takedown_minutes AS (
                  SELECT h.who, DIV(d.t_s, 60) AS minute, COUNT(*) AS n
                  FROM mapped h JOIN `{dataset}.deaths` d ON d.replay_id = h.replay_id AND d.game_date >= @cutover AND h.blizz IN UNNEST(d.killer_blizz_ids)
                  GROUP BY who, minute
                )
                SELECT a.who, a.minute, a.games,
                  IFNULL(u.presses, 0) / a.games AS presses, u.casts / a.games AS casts,
                  IFNULL(dd.n, 0) / a.games AS deaths, IFNULL(td.n, 0) / a.games AS takedowns
                FROM alive a
                LEFT JOIN ability_minutes u USING (who, minute)
                LEFT JOIN death_minutes dd USING (who, minute)
                LEFT JOIN takedown_minutes td USING (who, minute)
                ORDER BY a.who, a.minute'], [
                'blizz' => $blizzId,
                'region' => $region,
                'game_types' => array_values($filters['game_types']),
                'maps' => array_values($filters['maps']),
                'hero' => $filters['hero'],
                'cutover' => config('analytics.cutover'),
                'sample' => $threshold,
                'ability_hero' => $heroId,
                'builds' => $links->pluck('build')->unique()->map(fn ($b) => (int) $b)->values()->all(),
                'keys' => $links->map(fn ($link) => $link->build.':'.$link->abil_link)->values()->all(),
            ])['timeline'];
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
        return Cache::remember('analytics:layout', now()->addDay(), function () {
            return $this->run(['layout' => <<<'SQL'
                SELECT game_map, kind, type, team, CAST(ROUND(AVG(x)) AS INT64) AS x, CAST(ROUND(AVG(y)) AS INT64) AS y, COUNT(*) AS seen
                FROM `{dataset}.objectives`
                WHERE game_date >= @cutover AND kind IN ('town', 'building', 'camp') AND x IS NOT NULL
                GROUP BY game_map, kind, type, team, DIV(x, 3), DIV(y, 3)
                HAVING seen >= 3
                SQL], ['cutover' => config('analytics.cutover')])['layout'];
        });
    }

    /**
     * Shared CTEs. `games`: the player's games under the filters. `pg`: "you" and
     * "global" player-games in one shape (who, replay_id, blizz, team, hero_id, winner).
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
              SELECT 'global', replay_id, blizz_id, team, hero_id, winner
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

    /** Each team's biggest party per game, and how many of its players have no party size yet. */
    private function teamStacks(): string
    {
        return <<<'SQL'
            team_stacks AS (
              SELECT replay_id, team, MAX(IF(IFNULL(stack_size, 0) <= 1, 1, stack_size)) AS max_party, COUNTIF(stack_size IS NULL) AS unknown
              FROM `{dataset}.player_match`
              WHERE game_date >= @cutover
              GROUP BY 1, 2
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
                        COUNTIF(ARRAY_LENGTH(d.killer_blizz_ids) = 1 AND d.killer_blizz_ids[SAFE_OFFSET(0)] = pg.blizz) AS solo
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

                // Global only for the heroes the player has played, to keep the result small
                'hero_games' => $this->ctes().'
                    SELECT who, hero_id, COUNT(*) AS games
                    FROM pg WHERE hero_id IN (SELECT hero_id FROM games)
                    GROUP BY who, hero_id',

                'ability_usage' => $this->ctes().',
                    used AS (
                      -- Some abilities send two command indexes per press; the busiest one stands for the ability
                      SELECT pg.who, pg.hero_id, pg.replay_id, pg.blizz, u.abil_link,
                        SAFE_CAST(SPLIT(m.game_version, ".")[SAFE_OFFSET(3)] AS INT64) AS build,
                        ARRAY_AGG(STRUCT(u.uses, u.casts) ORDER BY u.uses DESC LIMIT 1)[OFFSET(0)] AS top,
                        MIN(u.first_use_s) AS first_use
                      FROM pg
                      JOIN `{dataset}.ability_usage` u ON u.replay_id = pg.replay_id AND u.blizz_id = pg.blizz AND u.game_date >= @cutover
                      JOIN `{dataset}.match` m ON m.replay_id = pg.replay_id AND m.game_date >= @cutover
                      WHERE pg.hero_id IN (SELECT hero_id FROM games)
                      GROUP BY pg.who, pg.hero_id, pg.replay_id, pg.blizz, u.abil_link, build
                    )
                    SELECT who, hero_id, build, abil_link, COUNT(*) AS games_used, SUM(top.uses) AS uses,
                      SUM(top.casts) AS casts, COUNTIF(top.casts IS NOT NULL) AS casts_games, AVG(first_use) AS avg_first_use
                    FROM used GROUP BY who, hero_id, build, abil_link',
            ],

            // Built nightly by the coaching script (HeroesProfileDeepAnalytics/bigquery/coaching.sql)
            'coaching' => [
                'signals' => 'SELECT signal, rules_v, computed_at, player_games, early_effect_pts, early_n, consistency_r, players_10,
                    r_win, partial_r, percentiles, confidence FROM `{dataset}.coaching_signal_stats`',

                'mine' => 'SELECT COUNT(*) AS games, SUM(win) AS wins,
                    SUM(fights) AS fights, SUM(fights_present) AS fights_present, SUM(lost_absent) AS lost_absent,
                    SUM(lost_absent_costly) AS lost_absent_costly, SUM(deaths) AS deaths, SUM(picked_alone) AS picked_alone,
                    SUM(caught_first) AS caught_first, SUM(overstay) AS overstay, SUM(tier_down_present) AS tier_down_present,
                    SUM(tier_down_lost) AS tier_down_lost, SUM(obj_events) AS obj_events, SUM(obj_present) AS obj_present,
                    AVG(heroic_delay_s) AS heroic_delay, COUNTIF(heroic_pick_s IS NOT NULL) AS heroic_games,
                    SUM(repeat_deaths) AS repeat_deaths, SUM(matchups_unadapted) AS matchups_unadapted,
                    AVG(IF(strong_camper AND camp_events > 0, camp_present / camp_events - hero_camp_rate, NULL)) AS camp_duty,
                    COUNTIF(strong_camper AND camp_events > 0) AS camp_games,
                    SUM(IF(bush_map, bush_deaths, 0)) AS bush_deaths, COUNTIF(bush_map) AS bush_games,
                    SAFE_DIVIDE(SUM(IF(mounts_used AND travel_s >= 30, unmounted_travel_s, 0)), SUM(IF(mounts_used AND travel_s >= 30, travel_s, 0))) AS unmounted_share,
                    COUNTIF(mounts_used AND travel_s >= 30) AS travel_games
                    FROM `{dataset}.coaching_player_game`
                    WHERE blizz_id = @blizz AND region = @region AND game_date >= @cutover
                      AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR game_type IN UNNEST(@game_types))
                      AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR game_map IN UNNEST(@maps))
                      AND (@hero = 0 OR hero_id = @hero)',

                // Your games one by one, newest first, for "Are you improving?" (last few vs earlier games)
                'recent' => 'SELECT replay_id, FORMAT_DATETIME("%Y-%m-%dT%H:%M:%S", game_date) AS game_date, win,
                      overstay, lost_absent, lost_absent_costly, SAFE_DIVIDE(obj_present, obj_events) AS objective_presence,
                      tier_down_present, caught_first, picked_alone, SAFE_DIVIDE(fights_present, fights) AS fight_presence,
                      heroic_delay_s, IF(mounts_used AND travel_s >= 30, SAFE_DIVIDE(unmounted_travel_s, travel_s), NULL) AS unmounted_travel,
                      IF(strong_camper AND camp_events > 0, camp_present / camp_events - hero_camp_rate, NULL) AS camp_duty,
                      repeat_deaths, matchups_unadapted, IF(bush_map, bush_deaths, NULL) AS bush_deaths
                    FROM `{dataset}.coaching_player_game`
                    WHERE blizz_id = @blizz AND region = @region AND game_date >= @cutover
                      AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR game_type IN UNNEST(@game_types))
                      AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR game_map IN UNNEST(@maps))
                      AND (@hero = 0 OR hero_id = @hero)
                    ORDER BY game_date DESC LIMIT 200',

                // The global sample under the same filters as "You": per-game values, and players with 5+ games
                'compare' => 'WITH g AS (
                      SELECT * FROM `{dataset}.coaching_player_game`
                      WHERE sample_bucket < @sample AND game_date >= @cutover
                        AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR game_type IN UNNEST(@game_types))
                        AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR game_map IN UNNEST(@maps))
                        AND (@hero = 0 OR hero_id = @hero)
                    ),
                    long AS (
                      SELECT blizz_id, s.signal, s.value FROM g, UNNEST([
                        STRUCT("overstay" AS signal, CAST(overstay AS FLOAT64) AS value),
                        STRUCT("missed_lost_fight", CAST(lost_absent AS FLOAT64)),
                        STRUCT("missed_costly_fight", CAST(lost_absent_costly AS FLOAT64)),
                        STRUCT("objective_presence", SAFE_DIVIDE(obj_present, obj_events)),
                        STRUCT("tier_down_fights", CAST(tier_down_present AS FLOAT64)),
                        STRUCT("caught_first", CAST(caught_first AS FLOAT64)),
                        STRUCT("picked_alone", CAST(picked_alone AS FLOAT64)),
                        STRUCT("fight_presence", SAFE_DIVIDE(fights_present, fights)),
                        STRUCT("heroic_delay", CAST(heroic_delay_s AS FLOAT64)),
                        STRUCT("unmounted_travel", IF(mounts_used AND travel_s >= 30, SAFE_DIVIDE(unmounted_travel_s, travel_s), NULL)),
                        STRUCT("camp_duty", IF(strong_camper AND camp_events > 0, camp_present / camp_events - hero_camp_rate, NULL)),
                        STRUCT("repeat_nemesis", CAST(repeat_deaths AS FLOAT64)),
                        STRUCT("matchup_unadapted", CAST(matchups_unadapted AS FLOAT64)),
                        STRUCT("bush_deaths", IF(bush_map, CAST(bush_deaths AS FLOAT64), NULL))
                      ]) s
                      WHERE s.value IS NOT NULL
                    ),
                    per_game AS (SELECT signal, APPROX_QUANTILES(value, 100) AS game_percentiles, COUNT(*) AS game_n FROM long GROUP BY 1),
                    per_player AS (
                      SELECT signal, APPROX_QUANTILES(v, 100) AS player_percentiles, COUNT(*) AS players
                      FROM (SELECT signal, blizz_id, AVG(value) AS v FROM long GROUP BY 1, 2 HAVING COUNT(*) >= 5)
                      GROUP BY 1
                    )
                    SELECT signal, game_percentiles, game_n, player_percentiles, IFNULL(players, 0) AS players
                    FROM per_game LEFT JOIN per_player USING (signal)',

                'events' => 'SELECT e.kind, e.replay_id, e.t_s, e.detail, e.hero_id, e.ref_hero_id, e.game_map, FORMAT_DATETIME("%Y-%m-%dT%H:%M:%S", e.game_date) AS game_date
                    FROM `{dataset}.coaching_events` e
                    JOIN `{dataset}.coaching_player_game` g ON g.replay_id = e.replay_id AND g.blizz_id = e.blizz_id
                    WHERE e.blizz_id = @blizz AND e.region = @region AND e.game_date >= @cutover
                      AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR g.game_type IN UNNEST(@game_types))
                      AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR e.game_map IN UNNEST(@maps))
                      AND (@hero = 0 OR e.hero_id = @hero)
                    QUALIFY ROW_NUMBER() OVER (PARTITION BY e.kind ORDER BY e.game_date DESC, e.t_s) <= 10',
            ],

            // Party size comes from heroesprofile.player via the loader (player_match.stack_size: 0 or 1 =
            // solo, else 2-5). A team's stack is its biggest party; games with any player still unknown
            // (not yet backfilled) are left out.
            'stacks' => [
                'party_size' => $this->ctes().', '.$this->teamStacks().'
                    SELECT pg.who, IF(IFNULL(p.stack_size, 0) <= 1, 1, p.stack_size) AS party, COUNT(*) AS games, AVG(IF(pg.winner, 1, 0)) AS win_rate
                    FROM pg
                    JOIN `{dataset}.player_match` p ON p.replay_id = pg.replay_id AND p.blizz_id = pg.blizz AND p.game_date >= @cutover
                    JOIN team_stacks own ON own.replay_id = pg.replay_id AND own.team = pg.team AND own.unknown = 0
                    GROUP BY 1, 2 ORDER BY 1, 2',

                'enemy_stack' => $this->ctes().', '.$this->teamStacks().'
                    SELECT pg.who, en.max_party AS enemy_party, COUNT(*) AS games, AVG(IF(pg.winner, 1, 0)) AS win_rate
                    FROM pg
                    JOIN team_stacks own ON own.replay_id = pg.replay_id AND own.team = pg.team AND own.unknown = 0
                    JOIN team_stacks en ON en.replay_id = pg.replay_id AND en.team != pg.team AND en.unknown = 0
                    GROUP BY 1, 2 ORDER BY 1, 2',

                'advantage' => $this->ctes().', '.$this->teamStacks().'
                    SELECT pg.who, GREATEST(-3, LEAST(3, en.max_party - own.max_party)) AS diff, COUNT(*) AS games, AVG(IF(pg.winner, 1, 0)) AS win_rate
                    FROM pg
                    JOIN team_stacks own ON own.replay_id = pg.replay_id AND own.team = pg.team AND own.unknown = 0
                    JOIN team_stacks en ON en.replay_id = pg.replay_id AND en.team != pg.team AND en.unknown = 0
                    GROUP BY 1, 2 ORDER BY 1, 2',

                // Global sample teams that faced a bigger enemy stack of 3+: how the teams that won played
                // compared with the teams that lost, from the coaching rows (team averages per player)
                'beat_stacks' => 'WITH '.$this->teamStacks().'
                    , teams AS (
                      SELECT c.replay_id, c.team, ANY_VALUE(c.win) AS win,
                        AVG(c.overstay) AS overstay, AVG(c.picked_alone) AS picked_alone, AVG(c.caught_first) AS caught_first,
                        AVG(c.lost_absent) AS lost_absent, AVG(SAFE_DIVIDE(c.fights_present, c.fights)) AS fight_presence,
                        AVG(SAFE_DIVIDE(c.obj_present, c.obj_events)) AS objective_presence, AVG(c.camp_present) AS camps,
                        AVG(c.tier_down_present) AS tier_down_fights, AVG(c.deaths) AS deaths, AVG(c.bush_deaths) AS bush_deaths,
                        AVG(IF(c.mounts_used AND c.travel_s >= 30, SAFE_DIVIDE(c.unmounted_travel_s, c.travel_s), NULL)) AS unmounted_travel
                      FROM `{dataset}.coaching_player_game` c
                      JOIN team_stacks own ON own.replay_id = c.replay_id AND own.team = c.team AND own.unknown = 0
                      JOIN team_stacks en ON en.replay_id = c.replay_id AND en.team != c.team AND en.unknown = 0
                      WHERE c.sample_bucket < @sample AND c.game_date >= @cutover AND en.max_party >= 3 AND en.max_party > own.max_party
                        AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR c.game_type IN UNNEST(@game_types))
                        AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR c.game_map IN UNNEST(@maps))
                      GROUP BY 1, 2
                    )
                    SELECT win, COUNT(*) AS teams, AVG(overstay) AS overstay, AVG(picked_alone) AS picked_alone, AVG(caught_first) AS caught_first,
                      AVG(lost_absent) AS lost_absent, AVG(fight_presence) AS fight_presence, AVG(objective_presence) AS objective_presence,
                      AVG(camps) AS camps, AVG(tier_down_fights) AS tier_down_fights, AVG(deaths) AS deaths, AVG(bush_deaths) AS bush_deaths,
                      AVG(unmounted_travel) AS unmounted_travel
                    FROM teams GROUP BY win',

                // Your own play in games against a bigger enemy stack of 3+, compared with your other games
                'mine_vs_stacks' => 'WITH '.$this->teamStacks().'
                    SELECT en.max_party >= 3 AND en.max_party > own.max_party AS facing_stack, COUNT(*) AS games, AVG(c.win) AS win_rate,
                      AVG(c.overstay) AS overstay, AVG(c.picked_alone) AS picked_alone, AVG(c.caught_first) AS caught_first,
                      AVG(c.lost_absent) AS lost_absent, AVG(SAFE_DIVIDE(c.fights_present, c.fights)) AS fight_presence,
                      AVG(SAFE_DIVIDE(c.obj_present, c.obj_events)) AS objective_presence, AVG(c.camp_present) AS camps,
                      AVG(c.tier_down_present) AS tier_down_fights, AVG(c.deaths) AS deaths, AVG(c.bush_deaths) AS bush_deaths,
                      AVG(IF(c.mounts_used AND c.travel_s >= 30, SAFE_DIVIDE(c.unmounted_travel_s, c.travel_s), NULL)) AS unmounted_travel
                    FROM `{dataset}.coaching_player_game` c
                    JOIN team_stacks own ON own.replay_id = c.replay_id AND own.team = c.team AND own.unknown = 0
                    JOIN team_stacks en ON en.replay_id = c.replay_id AND en.team != c.team AND en.unknown = 0
                    WHERE c.blizz_id = @blizz AND c.region = @region AND c.game_date >= @cutover
                      AND (IFNULL(ARRAY_LENGTH(@game_types), 0) = 0 OR c.game_type IN UNNEST(@game_types))
                      AND (IFNULL(ARRAY_LENGTH(@maps), 0) = 0 OR c.game_map IN UNNEST(@maps))
                      AND (@hero = 0 OR c.hero_id = @hero)
                    GROUP BY 1',
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
        $client = $this->client ??= new BigQueryClient(['projectId' => config('analytics.bigquery_project')]);
        $dataset = config('analytics.bigquery_project').'.'.config('analytics.bigquery_dataset');

        // Element types for the array params, so an empty filter list is still ARRAY<INT64>
        $types = array_intersect_key(['game_types' => 'INT64', 'maps' => 'INT64', 'builds' => 'INT64', 'keys' => 'STRING'], $params);

        $jobs = [];
        foreach ($queries as $name => $sql) {
            $config = $client->query(str_replace('{dataset}', $dataset, $sql))
                ->parameters($params)
                ->setParamTypes($types)
                ->location(config('analytics.bigquery_location'));
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
     * Turns per-build ability link counts into named abilities via ability_links. A game
     * uses the mapping of its own build, or the newest mapped build before it (links rarely
     * move between patches). Links with no name (move orders, internal helpers) are dropped.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $heroGames
     * @return array<int, array<string, mixed>>
     */
    private function namedAbilities(array $rows, array $heroGames): array
    {
        $builds = AbilityLink::query()->distinct()->orderBy('build')->pluck('build')->all();
        if ($builds === [] || $rows === []) {
            return [];
        }

        $mappedBuild = function (?int $build) use ($builds): int {
            $match = $builds[0];
            foreach ($builds as $candidate) {
                if ($build !== null && $candidate <= $build) {
                    $match = $candidate;
                }
            }

            return $match;
        };

        $needed = array_unique(array_map(fn ($row) => $mappedBuild($row['build'] === null ? null : (int) $row['build']), $rows));
        $links = AbilityLink::whereIn('build', $needed)
            ->whereNotNull('name')
            ->get(['build', 'abil_link', 'ability_id', 'hero_id', 'slot', 'name', 'icon', 'confidence'])
            ->keyBy(fn ($link) => $link->build.':'.$link->abil_link);

        $games = [];
        foreach ($heroGames as $row) {
            $games[$row['who'].':'.$row['hero_id']] = (int) $row['games'];
        }

        $named = [];
        foreach ($rows as $row) {
            $link = $links[$mappedBuild($row['build'] === null ? null : (int) $row['build']).':'.$row['abil_link']] ?? null;
            if (! $link) {
                continue;
            }

            $key = $row['who'].':'.$row['hero_id'].':'.$link->ability_id;
            $entry = $named[$key] ?? [
                'who' => $row['who'],
                'hero_id' => (int) $row['hero_id'],
                'ability_id' => $link->ability_id,
                'name' => $link->name,
                'slot' => $link->slot,
                'icon' => $link->icon,
                'confidence' => $link->confidence,
                'generic' => $link->hero_id === null,
                'games' => $games[$row['who'].':'.$row['hero_id']] ?? 0,
                'games_used' => 0,
                'uses' => 0,
                'casts' => 0,
                'casts_games' => 0,
                'first_use_total' => 0,
            ];
            $entry['games_used'] += (int) $row['games_used'];
            $entry['uses'] += (int) $row['uses'];
            $entry['casts'] += (int) ($row['casts'] ?? 0);
            $entry['casts_games'] += (int) $row['casts_games'];
            $entry['first_use_total'] += (float) $row['avg_first_use'] * (int) $row['games_used'];
            $named[$key] = $entry;
        }

        return array_values(array_map(function ($entry) {
            $entry['avg_first_use'] = $entry['games_used'] ? $entry['first_use_total'] / $entry['games_used'] : null;
            unset($entry['first_use_total']);

            return $entry;
        }, $named));
    }

    /**
     * Adds each player's current battletag without the #number, unless they have opted out.
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

        return array_map(fn ($row) => $row + ['battletag' => isset($tags[(int) $row['blizz_id']]) ? explode('#', $tags[(int) $row['blizz_id']])[0] : null], $rows);
    }
}
