<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Captures a small sample of each public endpoint's real response, so fixtures can
 * be written against the actual shape instead of a guess.
 *
 * Calls the controller directly rather than issuing an HTTP request: no API key,
 * no running server, and the fixture and quota middleware are bypassed — otherwise
 * an account on test data would capture the very fixture we are trying to verify.
 *
 * Read-only, and every list is truncated to --rows so this samples structure
 * rather than dumping data.
 */
class CaptureApiSamples extends Command
{
    protected $signature = 'api:capture-samples
                            {--endpoint=* : Registry keys to capture. Defaults to all routed public endpoints.}
                            {--rows=0 : Maximum items kept per list. 0 keeps everything, which is the default so a sample shows the real cardinality.}
                            {--query=* : Extra query parameters as key=value, applied to every endpoint captured.}
                            {--out= : Directory to write to. Defaults to storage/app/api-samples.}
                            {--raw : Skip anonymisation. Never use for anything that will become a committed fixture.}';

    protected $description = 'Write a truncated sample of each public API response for fixture authoring';

    /**
     * Fields carrying a real person or match. Replaced with stable fakes so a
     * sample can become a committed fixture without shipping player data.
     *
     * Deliberately not `name` — heroes, maps and talents all use it.
     *
     * Every field here is one that exists in this codebase — do not add a field
     * on the assumption it might. A field NOT listed is written through
     * untouched, so read the capture before promoting it to a fixture; the
     * replacement report is there to make an omission visible.
     */
    private const IDENTIFYING_FIELDS = [
        'battletag' => 'battletag',
        'blizz_id' => 'id',
        'region' => 'region',
    ];

    /** @var array<string, array<string, mixed>> original value => replacement, per field */
    private array $replacements = [];

    /** Endpoints that need parameters before they will answer at all. */
    private const DEFAULT_QUERY = [
        'mmr_tier' => ['game_type' => 'sl', 'mmr' => 2400],
    ];

    public function handle(): int
    {
        $rows = max(0, (int) $this->option('rows'));
        $directory = $this->option('out') ?: storage_path('app/api-samples');
        $only = $this->option('endpoint');

        File::ensureDirectoryExists($directory);

        $written = 0;
        $failed = 0;

        foreach ($this->publicRoutes() as $endpoint => $route) {
            if ($only !== [] && ! in_array($endpoint, $only, true)) {
                continue;
            }

            try {
                $payload = $this->capture($route, $endpoint);
            } catch (Throwable $e) {
                $this->error($endpoint.' — '.Str::limit($e->getMessage(), 120));
                $failed++;

                continue;
            }

            $path = $directory.'/'.$endpoint.'.json';

            $payload = $this->truncate($payload, $rows);

            if (! $this->option('raw')) {
                $payload = $this->scrub($payload);
            }

            File::put($path, json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));

            $this->line('<info>'.$endpoint.'</info> → '.$path);
            $written++;
        }

        $this->newLine();

        if ($this->option('raw')) {
            $this->warn('Captured RAW. These contain real player data — do not commit them as fixtures.');
        } else {
            $this->reportReplacements();
        }

        $this->info($written.' captured'.($failed > 0 ? ', '.$failed.' failed' : '').'.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return array<string, Route> keyed by registry endpoint */
    private function publicRoutes(): array
    {
        $routes = [];

        foreach (Router::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            foreach ($route->gatherMiddleware() as $middleware) {
                if (is_string($middleware) && str_starts_with($middleware, 'api.quota:')) {
                    $endpoint = substr($middleware, strlen('api.quota:'));
                    $routes[$endpoint] ??= $route;
                }
            }
        }

        return $routes;
    }

    private function capture(Route $route, string $endpoint): mixed
    {
        $query = array_merge(self::DEFAULT_QUERY[$endpoint] ?? [], $this->queryOverrides());

        $request = Request::create('/'.ltrim($route->uri(), '/'), 'GET', $query);
        app()->instance('request', $request);

        [$class, $method] = explode('@', $route->getActionName());

        // A route parameter such as {replayID} is an argument, not a query value.
        // Supply it from --query so one flag covers both shapes.
        $arguments = ['request' => $request];

        foreach ($route->parameterNames() as $name) {
            if (! array_key_exists($name, $query)) {
                throw new RuntimeException("Endpoint [{$endpoint}] needs --query={$name}=<value>.");
            }

            $arguments[$name] = $query[$name];
        }

        $response = app()->call([app($class), $method], $arguments);

        if (method_exists($response, 'getContent')) {
            return json_decode($response->getContent(), true);
        }

        return $response;
    }

    /**
     * Replaces identifying values with stable fakes. The same battletag maps to
     * the same fake everywhere in the run, so relationships between records
     * survive and the fixture still reads coherently.
     */
    private function scrub(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            $scrubbed = [];

            foreach ($value as $childKey => $child) {
                $scrubbed[$childKey] = $this->scrub($child, is_string($childKey) ? $childKey : $key);
            }

            return $scrubbed;
        }

        if ($key === null || $value === null || ! isset(self::IDENTIFYING_FIELDS[$key])) {
            return $value;
        }

        $original = (string) $value;

        if (isset($this->replacements[$key][$original])) {
            return $this->replacements[$key][$original];
        }

        $index = count($this->replacements[$key] ?? []) + 1;

        $replacement = match (self::IDENTIFYING_FIELDS[$key]) {
            'battletag' => 'ExamplePlayer'.$index.'#0000',
            // Pinned to one real region rather than a fake number: there are only
            // four valid values, and a made-up one would fail a consumer's own
            // validation against the fixture.
            'region' => is_numeric($value) ? 1 : 'NA',
            default => 9000000 + $index,
        };

        return $this->replacements[$key][$original] = $replacement;
    }

    private function reportReplacements(): void
    {
        if ($this->replacements === []) {
            $this->line('<comment>Nothing anonymised.</comment> If this endpoint returns player data, add its fields to IDENTIFYING_FIELDS.');

            return;
        }

        $this->table(
            ['Field', 'Distinct values replaced'],
            array_map(
                fn ($field, $values) => [$field, count($values)],
                array_keys($this->replacements),
                array_values($this->replacements)
            )
        );
    }

    /** @return array<string, string> */
    private function queryOverrides(): array
    {
        $overrides = [];

        foreach ($this->option('query') as $pair) {
            if (str_contains($pair, '=')) {
                [$key, $value] = explode('=', $pair, 2);

                // Accept either `Zemill#1940` or `Zemill%231940`. These go into the
                // request as decoded values, so a percent-encoded battletag would
                // otherwise be searched for literally and match nobody.
                $overrides[$key] = urldecode($value);
            }
        }

        return $overrides;
    }

    /** Keeps the shape, drops the volume. */
    private function truncate(mixed $value, int $rows): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            $items = $rows > 0 ? array_slice($value, 0, $rows) : $value;

            return array_map(fn ($item) => $this->truncate($item, $rows), $items);
        }

        return array_map(fn ($item) => $this->truncate($item, $rows), $value);
    }
}
