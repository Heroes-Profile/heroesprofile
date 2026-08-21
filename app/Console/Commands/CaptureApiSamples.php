<?php

namespace App\Console\Commands;

use App\Http\Middleware\ServeApiFixtures;
use App\Models\NGS\Player as NgsPlayer;
use App\Models\NGS\Replay as NgsReplay;
use App\Services\GlobalDataService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
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
                            {--promote : Write straight to resources/api-fixtures, so a captured endpoint is a finished fixture. Refused with --raw.}
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
        // The battletag with its discriminator stripped — so replacing only
        // `battletag` leaves the player's actual name sitting beside it.
        'split_battletag' => 'name',
        'blizz_id' => 'id',
        'region' => 'region',
        // A real replay id undoes the battletag replacement: `matches/{replayID}`
        // is public, so anyone can resolve the match and read the player straight
        // back out of it.
        'replayID' => 'replay',
        // Player activity. Reference dates — `release_date`, `last_updated` on a
        // hero — are not listed and stay real, because they describe the game
        // rather than a person.
        'game_date' => 'date',
    ];

    /** What every scrubbed player date collapses to, as existing fixtures have it. */
    private const PLACEHOLDER_DATE = '2020-01-01 00:00:00';

    /** @var array<string, array<string, mixed>> original value => replacement, per field */
    private array $replacements = [];

    /** Endpoints that need parameters before they will answer at all. */
    private const DEFAULT_QUERY = [
        'mmr_tier' => ['game_type' => 'sl', 'mmr' => 2400],
        'ngs_leaderboard_highest_average_stat' => ['stat' => 'hero_damage'],
        'ngs_leaderboard_highest_total_stat' => ['stat' => 'hero_damage'],
    ];

    public function handle(): int
    {
        $rows = max(0, (int) $this->option('rows'));
        $only = $this->option('endpoint');

        // Capturing and then hand-copying each file into place is two steps for
        // one job, and the copy is where an endpoint quietly ends up with no
        // fixture. --promote closes that, but never for a raw capture: those
        // carry real player data and must not become committed fixtures.
        if ($this->option('promote') && $this->option('raw')) {
            $this->error('--promote and --raw are mutually exclusive: a raw capture must never become a fixture.');

            return self::FAILURE;
        }

        $directory = $this->option('out')
            ?: ($this->option('promote')
                ? resource_path(ServeApiFixtures::DIRECTORY)
                : storage_path('app/api-samples'));

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
                // A crash inside a delegated controller says nothing about where
                // it happened, and these are all third-party-to-us call stacks.
                $where = $e instanceof RuntimeException
                    ? ''
                    : ' ('.basename($e->getFile()).':'.$e->getLine().')';

                $this->error($endpoint.' — '.Str::limit($e->getMessage(), 120).$where);
                $failed++;

                continue;
            }

            $path = $directory.'/'.$endpoint.'.json';

            $payload = $this->truncate($payload, $rows);

            if (! $this->option('raw')) {
                $payload = $this->scrub($payload);
            }

            // Promoting means this file ships as the documented shape of the
            // endpoint, so a capture that is not one must not be written. Both of
            // these look like successful captures from the outside: the internal
            // controllers answer a validation failure with HTTP 200 and a `status`
            // field, and an unnarrowed query can legitimately return nothing.
            if ($this->option('promote') && ($reason = $this->unfitForFixture($payload)) !== null) {
                $this->error($endpoint.' — not written: '.$reason);
                $failed++;

                continue;
            }

            File::put($path, json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));

            $this->line('<info>'.$endpoint.'</info> → '.$path);

            // Fixtures are served in full on every test-mode call, so a large one
            // is a cost paid forever. --rows keeps the shape and drops the bulk.
            if ($this->option('promote') && ($size = filesize($path)) > 1024 * 1024) {
                $this->warn('  '.$endpoint.' is '.round($size / 1024 / 1024, 1).'MB. Consider re-capturing it with --rows.');
            }

            $written++;
        }

        $this->newLine();

        if ($this->option('raw')) {
            $this->warn('Captured RAW. These contain real player data — do not commit them as fixtures.');
        } else {
            $this->reportReplacements();
        }

        if ($this->option('promote')) {
            $this->warn('Written straight to fixtures. Read the replacements above before committing: a field that is not anonymised is a field that ships.');
        }

        $this->info($written.' captured'.($failed > 0 ? ', '.$failed.' failed' : '').'.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Why this capture must not become a fixture, or null if it may.
     *
     * A fixture is what an account on test data receives, so an empty one
     * documents an endpoint as returning nothing and an error one documents a
     * shape no working call ever produces.
     */
    private function unfitForFixture(mixed $payload): ?string
    {
        if ($payload === [] || $payload === null) {
            return 'the endpoint returned nothing. Narrow it with --query, or pick a player with data for it.';
        }

        if (is_array($payload) && ($payload['status'] ?? null) === 'failure to validate inputs') {
            $errors = $payload['errors'] ?? [];

            return 'the endpoint rejected the request — '
                .(is_array($errors) ? implode(' ', $errors) : 'no reason given');
        }

        return null;
    }

    /** @return array<string, Route> keyed by registry endpoint */
    private function publicRoutes(): array
    {
        $routes = [];

        foreach (Router::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            // Keyed off either middleware: NGS endpoints carry fixtures but no
            // quota, so looking only for api.quota would skip them.
            foreach (['api.quota:', 'api.fixtures:'] as $alias) {
                foreach ($route->gatherMiddleware() as $middleware) {
                    if (is_string($middleware) && str_starts_with($middleware, $alias)) {
                        $endpoint = substr($middleware, strlen($alias));
                        $routes[$endpoint] ??= $route;
                    }
                }
            }
        }

        return $routes;
    }

    private function capture(Route $route, string $endpoint): mixed
    {
        $query = array_merge($this->defaultsFor($endpoint), $this->queryOverrides());

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

        // The public controllers answer a bad request with a real status code, so
        // this catches our own error envelopes before they can be written as the
        // documented shape of the endpoint.
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 400) {
            $body = method_exists($response, 'getContent') ? json_decode($response->getContent(), true) : null;

            throw new RuntimeException(
                'the endpoint answered '.$response->getStatusCode().' — '
                .($body['error']['message'] ?? 'no message given')
            );
        }

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
            'name' => 'ExamplePlayer'.$index,
            // Pinned to one real region rather than a fake number: there are only
            // four valid values, and a made-up one would fail a consumer's own
            // validation against the fixture.
            'region' => is_numeric($value) ? 1 : 'NA',
            // Its own range, so a fake replay id is never mistaken for a blizz_id.
            'replay' => 90000000 + $index,
            'date' => self::PLACEHOLDER_DATE,
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

    /**
     * Parameters an endpoint needs before it will answer. Most are fixed; NGS
     * needs a season, division, team and round that actually played, so it looks
     * one up rather than making the caller know a valid combination.
     *
     * @return array<string, mixed>
     */
    private function defaultsFor(string $endpoint): array
    {
        if (! in_array($endpoint, ['ngs_match', 'ngs_hero_stat', 'ngs_player_profile', 'ngs_replay_data'], true)) {
            return self::DEFAULT_QUERY[$endpoint] ?? [];
        }

        $replay = NgsReplay::orderByDesc('replayID')->first();

        if ($replay === null) {
            return [];
        }

        $defaults = [
            'season' => $replay->season,
            'division' => $replay->division_0,
            'team' => $replay->team_0_name,
            'round' => $replay->round,
        ];

        if ($endpoint === 'ngs_replay_data') {
            $defaults = ['replayID' => $replay->replayID];
        }

        if ($endpoint === 'ngs_player_profile') {
            $playerId = NgsPlayer::where('replayID', $replay->replayID)->value('battletag');

            $defaults = [
                'battletag' => DB::connection('heroesprofile_ngs')
                    ->table('battletags')
                    ->where('player_id', $playerId)
                    ->value('battletag'),
                'season' => $replay->season,
            ];
        }

        if ($endpoint === 'ngs_hero_stat') {
            $hero = NgsPlayer::where('replayID', $replay->replayID)->value('hero');

            $defaults = [
                'season' => $replay->season,
                'division' => $replay->division_0,
                'hero' => app(GlobalDataService::class)->getHeroesByID()[$hero]->name ?? null,
            ];
        }

        $this->line('  <comment>using</comment> '.json_encode($defaults));

        return $defaults;
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
                $value = urldecode($value);

                // `selectedtalents[1]=2859` has to reach the controller as a nested
                // array. Without this it arrives as a parameter literally named
                // `selectedtalents[1]`, which nothing reads.
                if (preg_match('/^([^\[\]]+)\[([^\]]*)\]$/', $key, $matches)) {
                    if ($matches[2] === '') {
                        $overrides[$matches[1]][] = $value;
                    } else {
                        $overrides[$matches[1]][$matches[2]] = $value;
                    }

                    continue;
                }

                $overrides[$key] = $value;
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
