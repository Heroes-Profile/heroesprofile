<?php

namespace App\Console\Commands;

use App\Http\Middleware\ServeApiFixtures;
use App\Support\ApiSpecConfig;
use App\Support\JsonSchemaFromSample;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as Router;

/**
 * Generates the public API's OpenAPI document.
 *
 * Assembled from three sources, none of them a hand-maintained spec file:
 *
 *  - **paths and methods** from the route table, so the document cannot describe
 *    an endpoint that does not exist, or miss one that does;
 *  - **parameters** from `config/api_spec.php`, because the rules defining them
 *    are built at runtime inside the delegated controllers where nothing can read
 *    them;
 *  - **response schemas** from the fixtures, which are captured from live output.
 *
 * It fails rather than emitting a partial document: a routed endpoint with no
 * config entry, or no fixture and no declared response, stops the build. That is
 * the same guarantee an inference-based generator offers, enforced by a check we
 * can see into.
 */
class BuildApiSpec extends Command
{
    protected $signature = 'api:build-spec
                            {--out= : Where to write. Defaults to public/spec/heroesprofile-v1.json.}
                            {--check : Report problems and write nothing. For CI.}';

    protected $description = 'Generate the public API OpenAPI document from routes, config and fixtures';

    /** Routes that answer without a key, and so carry no security requirement. */
    private const KEYLESS = [
        'api.external.upload',
        'api.external.replays.fingerprint',
        'api.external.replays.parsed',
        'api.external.prematch',
    ];

    public function handle(): int
    {
        $config = config('api_spec');
        $problems = [];
        $paths = [];

        foreach ($this->publicRoutes() as $name => $route) {
            $endpoint = $config['endpoints'][$name] ?? null;

            if ($endpoint === null) {
                $problems[] = [$name, $route->uri(), 'No entry in config/api_spec.php'];

                continue;
            }

            $group = $this->groupOf($name, $config);

            if ($group === null) {
                $problems[] = [$name, $route->uri(), 'Not in any section of config/api_spec.php `groups`'];

                continue;
            }

            $operation = $this->operation($name, $route, $endpoint, $config);

            if (is_string($operation)) {
                $problems[] = [$name, $route->uri(), $operation];

                continue;
            }

            $operation['tags'] = [$group];

            $path = '/'.$this->specPath($route);
            $paths[$path][strtolower($route->methods()[0])] = $operation;
        }

        if ($problems !== []) {
            $this->error(count($problems).' endpoints could not be documented:');
            $this->table(['Route', 'URI', 'Problem'], $problems);

            return self::FAILURE;
        }

        ksort($paths);

        $document = [
            'openapi' => '3.1.0',
            'info' => $config['info'],
            'servers' => $config['servers'],
            'components' => [
                'securitySchemes' => [
                    'apiKey' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'description' => 'An API key from your account page, sent as `Authorization: Bearer <key>`.',
                    ],
                ],
            ],
            'security' => [['apiKey' => []]],
            // Declared in config order, which is the order the docs show them in.
            // Alphabetising these would bury Reference behind Global Hero Stats.
            'tags' => array_map(
                fn (string $name) => ['name' => $name],
                array_keys($config['groups'] ?? [])
            ),
            'paths' => $paths,
        ];

        if ($this->option('check')) {
            $this->info(count($paths).' paths documented. Nothing written.');

            return self::SUCCESS;
        }

        $out = $this->option('out') ?: public_path('spec/heroesprofile-v1.json');
        File::ensureDirectoryExists(dirname($out));
        File::put($out, json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->info(count($paths).' paths written to '.$out);

        return self::SUCCESS;
    }

    /** Which documentation section an endpoint belongs to, or null if none claims it. */
    private function groupOf(string $name, array $config): ?string
    {
        foreach ($config['groups'] ?? [] as $group => $names) {
            if (in_array($name, $names, true)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * One operation, or a string saying why it could not be built.
     *
     * @return array<string, mixed>|string
     */
    private function operation(string $name, Route $route, array $endpoint, array $config): array|string
    {
        $responses = $endpoint['responses'] ?? $this->responsesFromFixture($route);

        if (is_string($responses)) {
            return $responses;
        }

        // A cold global query answers 202 with a job id instead of data. It is a
        // second documented shape per endpoint, and nothing in the code says so.
        if ($endpoint['async'] ?? false) {
            $responses['202'] = $this->jobAccepted();
        }

        $operation = [
            'summary' => $endpoint['summary'] ?? '',
            'operationId' => $name,
            'parameters' => $this->parameters($route, $endpoint, $config),
            'responses' => $responses,
        ];

        if ($registry = $this->middlewareArgument($route, 'api.quota')) {
            $operation['x-endpoint-key'] = $registry;
        }

        if ($rateLimit = $this->rateLimitNote($name)) {
            $operation['x-rate-limit'] = $rateLimit;
        }

        // The site page this endpoint answers for. Quicker than any description at
        // conveying what the data actually is — the reader can go look at it.
        if ($page = $endpoint['page'] ?? null) {
            $operation['x-site-page'] = $page;
        }

        if (in_array($name, self::KEYLESS, true)) {
            $operation['security'] = [];
        }

        return $operation;
    }

    /**
     * Path parameters come from the URI, so they cannot be forgotten; the rest are
     * declared.
     *
     * @return array<int, array<string, mixed>>
     */
    private function parameters(Route $route, array $endpoint, array $config): array
    {
        // Shared with the request handling, so the document cannot claim an arity
        // the code does not implement. The `except` removals matter here: the
        // shared globals rules *validate* `role`, `groupsize` and `statfilter` on
        // endpoints whose controllers never look at them, so without them the docs
        // advertise filters that are accepted and then silently do nothing — worse
        // than rejecting them, because the caller believes the result is filtered.
        $declared = ApiSpecConfig::resolve($endpoint, $config);

        // Applied by middleware across the board, so declaring it per endpoint would
        // be fifty copies of one line that drift apart. Exempt endpoints are the ones
        // that do not answer JSON.
        if (! in_array($route->getName(), $config['csv_exempt'] ?? [], true)) {
            $declared['mode'] = $config['csv'] ?? [];
        }

        $parameters = [];

        foreach ($route->parameterNames() as $pathParameter) {
            $spec = $declared[$pathParameter] ?? [];
            unset($declared[$pathParameter]);

            $parameters[] = $this->parameter($pathParameter, $spec + ['required' => true], 'path');
        }

        foreach ($declared as $parameterName => $spec) {
            $parameters[] = $this->parameter($parameterName, $spec, 'query');
        }

        return $parameters;
    }

    /** @return array<string, mixed> */
    private function parameter(string $name, array $spec, string $in): array
    {
        $schema = ['type' => $spec['type'] ?? 'string'];

        if (isset($spec['enum'])) {
            $schema['enum'] = $spec['enum'];
        }

        $parameter = [
            'name' => $name,
            'in' => $in,
            'required' => (bool) ($spec['required'] ?? false),
            'schema' => $schema,
        ];

        // A comma-separated list, which an enum on its own contradicts: a client
        // generated from that would reject `sl,tl` even though the API accepts it.
        // `form` + no explode is the OpenAPI spelling of one comma-joined value.
        if ($spec['multi'] ?? false) {
            $parameter['schema'] = ['type' => 'array', 'items' => $schema];
            $parameter['style'] = 'form';
            $parameter['explode'] = false;
        }

        if (isset($spec['description'])) {
            $parameter['description'] = $spec['description'];
        }

        if (isset($spec['example'])) {
            $parameter['example'] = $spec['example'];
        }

        return $parameter;
    }

    /**
     * The cache-miss answer shared by every global statistics endpoint. Quota is
     * charged here, once; polling the job afterwards is free.
     *
     * @return array<string, mixed>
     */
    private function jobAccepted(): array
    {
        return [
            'description' => 'The result was not cached. Poll the job for it — polling costs no quota.',
            'headers' => [
                'Location' => [
                    'description' => 'Where to collect the result.',
                    'schema' => ['type' => 'string'],
                ],
                'Retry-After' => [
                    'description' => 'Suggested seconds between polls.',
                    'schema' => ['type' => 'integer'],
                ],
            ],
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => ['job_id' => ['type' => 'string']],
                        'required' => ['job_id'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|string the responses, or why there are none
     */
    private function responsesFromFixture(Route $route): array|string
    {
        $key = $this->middlewareArgument($route, 'api.fixtures');

        if ($key === null) {
            return 'No fixture middleware, and no `responses` declared in config';
        }

        $path = ServeApiFixtures::path($key);

        if (! is_file($path)) {
            return "No fixture at resources/api-fixtures/{$key}.json to derive a schema from";
        }

        $sample = json_decode((string) file_get_contents($path), true);

        if ($sample === null && json_last_error() !== JSON_ERROR_NONE) {
            return "Fixture for [{$key}] is not valid JSON";
        }

        return [
            '200' => [
                'description' => 'Success',
                'content' => [
                    'application/json' => [
                        'schema' => $this->annotate(
                            JsonSchemaFromSample::describe($sample),
                            config('api_spec.fields', [])
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * Attaches field descriptions to a derived schema, by property name.
     *
     * A fixture describes shape and nothing else, so a derived schema knows
     * `game_length` is an integer but not that it counts seconds. Names are
     * matched wherever they appear, however deeply nested, because the same field
     * means the same thing in every endpoint that returns it.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, string>  $fields
     * @return array<string, mixed>
     */
    private function annotate(array $schema, array $fields): array
    {
        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = $this->annotate($schema['items'], $fields);
        }

        if (! isset($schema['properties']) || ! is_array($schema['properties'])) {
            return $schema;
        }

        foreach ($schema['properties'] as $name => $property) {
            if (! is_array($property)) {
                continue;
            }

            $property = $this->annotate($property, $fields);

            if (isset($fields[$name]) && ! isset($property['description'])) {
                $property['description'] = $fields[$name];
            }

            $schema['properties'][$name] = $property;
        }

        return $schema;
    }

    /**
     * The routes are mounted twice — on the API subdomain and under a path prefix
     * so they can be exercised before DNS moves. The spec documents the subdomain,
     * which is where they live permanently.
     */
    private function specPath(Route $route): string
    {
        $uri = $route->uri();

        foreach ([config('api.path').'/', 'v1/'] as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return substr($uri, strlen($prefix));
            }
        }

        return $uri;
    }

    /** @return array<string, Route> keyed by route name, one entry per endpoint */
    private function publicRoutes(): array
    {
        $routes = [];

        foreach (Router::getRoutes() as $route) {
            $name = $route->getName();

            if ($name === null || ! str_starts_with($name, 'api.external.')) {
                continue;
            }

            // Both mounts share a name. Prefer the subdomain one, which is what
            // the spec documents.
            if (isset($routes[$name]) && ! str_starts_with($route->uri(), 'v1/')) {
                continue;
            }

            $routes[$name] = $route;
        }

        ksort($routes);

        return $routes;
    }

    /** The `heroes_stats` in `api.quota:heroes_stats`, or null if absent. */
    /**
     * How this endpoint's per-minute limit differs from the plan's, if it does.
     *
     * The weekly allowance is per plan and lives in the registry, which is why it
     * is a key here rather than a number. The per-minute limit is not per plan at
     * all — it is decided per route in `config/api.php` — so it can be stated
     * outright, and it needs to be: nothing else in this document mentions rate
     * limiting, and two of these are an order of magnitude off the default.
     */
    private function rateLimitNote(string $routeName): ?string
    {
        $limits = config('api.rate_limits');

        if (in_array($routeName, $limits['batch_routes'] ?? [], true)) {
            return $limits['batch'].' requests a minute. One call runs a query per hero,'
                .' so this is deliberately far below the usual limit.';
        }

        if (isset($limits['routes'][$routeName])) {
            return $limits['routes'][$routeName].' requests a minute, on every plan,'
                .' rather than the usual '.$limits['default'].'.';
        }

        if (ApiSpecConfig::declaresParameter($routeName, 'group_by_map')) {
            return 'The usual '.$limits['default'].' requests a minute, dropping to '
                .$limits['batch'].' when `group_by_map=true` — that form runs a query per playable map.';
        }

        return null;
    }

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
