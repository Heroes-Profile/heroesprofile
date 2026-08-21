<?php

namespace App\Console\Commands;

use App\Http\Middleware\ServeApiFixtures;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;

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

    public function handle(): int
    {
        $problems = [];
        $checked = 0;

        foreach (Router::getRoutes() as $route) {
            $endpoint = $this->middlewareArgument($route, 'api.quota');

            if ($endpoint === null) {
                continue;
            }

            $checked++;

            if ($this->middlewareArgument($route, 'api.fixtures') !== $endpoint) {
                $problems[] = [$route->uri(), $endpoint, 'Missing api.fixtures middleware'];

                continue;
            }

            if (! ServeApiFixtures::exists($endpoint)) {
                $problems[] = [$route->uri(), $endpoint, 'No fixture at resources/'.ServeApiFixtures::DIRECTORY.'/'.$endpoint.'.*'];
            }
        }

        if ($problems !== []) {
            $this->error(count($problems).' of '.$checked.' public endpoints are not covered:');
            $this->table(['Route', 'Endpoint', 'Problem'], $problems);

            return self::FAILURE;
        }

        $this->info($checked === 0
            ? 'No public endpoints are routed yet. Nothing to check.'
            : $checked.' public endpoints all have fixtures.');

        return self::SUCCESS;
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
