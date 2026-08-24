<?php

namespace App\Console\Commands;

use App\Http\Middleware\ServeApiFixtures;
use App\Models\Api\ApiEndpoint;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Throwable;

/**
 * Guards the migration gate. An endpoint that charges quota but has no fixture
 * would serve live data to an account that has not activated it, which is exactly
 * what the gate exists to prevent.
 *
 * Run by hand after adding a public endpoint.
 */
class CheckApiFixtures extends Command
{
    protected $signature = 'api:check-fixtures';

    protected $description = 'Verify every public API endpoint has a fixture file and the fixture middleware';

    /**
     * Registry rows that are deliberately not routed. The old API exposed seven
     * NGS endpoints; these seven keys it never routed are sunset candidates,
     * recorded in the plan rather than built. Everything else missing a route is
     * a gap, not a decision.
     */
    /**
     * Registry rows that are not routed *yet*. Reported, but do not fail the
     * command: they are known outstanding work rather than drift, and a check
     * that is permanently red is a check people stop reading.
     *
     * Distinct from UNROUTED_BY_DESIGN below, which is work deliberately never
     * done. Anything here should eventually leave this list by being built.
     */
    private const UNROUTED_PENDING = [
        // The last Milestone I endpoint. Tracked in the plan.
        'ngs_games_upload',
    ];

    private const UNROUTED_BY_DESIGN = [
        'ngs_standings',
        'ngs_divisions',
        'ngs_teams',
        'ngs_single_team',
        'ngs_single_player',
        'ngs_division_single',
        'ngs_team_match_history',
        // Superseded, not missing: `players/matches` carries the stat line the
        // old `/Player/Replays` returned, now that its scores join is restored.
        'player_replays',
        // Retired. Nothing on the site produces per-player stat averages, and
        // `players/heroes` covers the same ground for a consumer.
        'player_prematch',
        // Retired. The feature is not in use — its own web routes are commented
        // out, so the page it belongs to is unreachable on the site.
        'compare',
    ];

    public function handle(): int
    {
        $problems = [];
        $checked = 0;
        $routed = [];

        foreach (Router::getRoutes() as $route) {
            $quota = $this->middlewareArgument($route, 'api.quota');
            $fixture = $this->middlewareArgument($route, 'api.fixtures');

            if ($quota === null && $fixture === null) {
                continue;
            }

            $routed[] = $quota ?? $fixture;
            $checked++;

            // An endpoint that charges quota must serve fixtures, or an account on
            // test data would fall through to live results.
            if ($quota !== null && $fixture !== $quota) {
                $problems[] = [$route->uri(), $quota, 'Missing api.fixtures middleware'];

                continue;
            }

            if ($fixture !== null && ! ServeApiFixtures::exists($fixture)) {
                $problems[] = [$route->uri(), $fixture, 'No fixture at resources/'.ServeApiFixtures::DIRECTORY.'/'.$fixture.'.*'];
            }
        }

        $failed = false;

        if ($problems !== []) {
            $this->error(count($problems).' of '.$checked.' public endpoints are not covered:');
            $this->table(['Route', 'Endpoint', 'Problem'], $problems);
            $failed = true;
        } else {
            $this->info($checked === 0
                ? 'No public endpoints are routed yet. Nothing to check.'
                : $checked.' public endpoints all have fixtures.');
        }

        return $this->reportUnrouted($routed) || $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Registry rows with no route.
     *
     * The registry is what the pricing table and the docs nav are generated
     * from, so a row with no endpoint behind it is an endpoint the portal
     * advertises and the API answers 404 for. Checking routes against fixtures
     * alone could never catch that, which is how it drifted this far.
     *
     * @param  array<int, string>  $routed
     * @return bool whether anything is missing
     */
    private function reportUnrouted(array $routed): bool
    {
        try {
            $registry = ApiEndpoint::orderBy('group_sort')->orderBy('sort')->get(['endpoint', 'group_name']);
        } catch (Throwable $e) {
            // The rest of this command needs no database. Losing one check is
            // better than making the whole thing unrunnable without one.
            $this->warn('Could not read the endpoint registry, so unrouted rows were not checked: '.$e->getMessage());

            return false;
        }

        $unrouted = $registry
            ->reject(fn ($row) => in_array($row->endpoint, $routed, true))
            ->reject(fn ($row) => in_array($row->endpoint, self::UNROUTED_BY_DESIGN, true));

        $pending = $unrouted->filter(fn ($row) => in_array($row->endpoint, self::UNROUTED_PENDING, true));
        $missing = $unrouted->reject(fn ($row) => in_array($row->endpoint, self::UNROUTED_PENDING, true));

        if ($pending->isNotEmpty()) {
            $this->warn($pending->count().' registry endpoint(s) still to be built: '.$pending->pluck('endpoint')->implode(', '));
        }

        if ($missing->isEmpty()) {
            $this->info('Every registry endpoint has a route, or is accounted for.');

            return false;
        }

        $this->error($missing->count().' registry endpoints have no route, but are advertised by the pricing table and docs nav:');
        $this->table(
            ['Endpoint', 'Group'],
            $missing->map(fn ($row) => [$row->endpoint, $row->group_name])->all()
        );

        return true;
    }

    /** The `heroes_stats` in `api.quota:heroes_stats`, or null if absent. */
    private function middlewareArgument(Route $route, string $alias): ?string
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, $alias.':')) {
                return substr($middleware, strlen($alias) + 1);
            }
        }

        return null;
    }
}
