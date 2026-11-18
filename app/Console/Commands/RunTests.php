<?php

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Runs the test suites against the local test container. Never touches a database itself:
 * it starts the container if needed and hands over to PHPUnit, which loads .env.testing and
 * refuses to run against anything but 127.0.0.1:33061.
 */
class RunTests extends Command
{
    protected $signature = 'hp:test
        {suite=full : full, user, backend, unit or schema}
        {--filter= : Only tests whose name matches this (PHPUnit --filter)}
        {--update-snapshots : Re-record the response snapshots instead of comparing them}';

    protected $description = 'Run the test suites against the local test database container';

    private const SUITES = [
        'full' => 'Everything below',
        'user' => 'What a visitor or API client sees: every page, data endpoint, public API call and esports page, compared to the recorded snapshots',
        'backend' => 'Background machinery and writes with external services simulated: global stats through Cloud Tasks, Deep Analytics on BigQuery, API portal and admin, billing on Stripe, help chat on Claude',
        'unit' => 'Pure logic (support classes, input rules). No database needed',
        'schema' => 'Every model\'s table exists in the migrated database',
    ];

    private const COMPOSE_FILE = 'docker-compose/docker-compose.testing.yaml';

    public function handle(): int
    {
        $suite = $this->argument('suite');

        if (! array_key_exists($suite, self::SUITES)) {
            $this->error("Unknown suite \"{$suite}\".");
            $this->table(['Suite', 'Runs'], collect(self::SUITES)->map(fn ($runs, $name) => [$name, $runs])->values());

            return self::INVALID;
        }

        // Artisan loaded .env into this process; a child would inherit it and those values would beat
        // .env.testing. Unset every .env key (false removes it) so PHPUnit sees only .env.testing.
        $env = array_fill_keys($this->dotEnvKeys(), false);
        $env['APP_ENV'] = 'testing';

        if ($suite === 'unit') {
            $env['TEST_SKIP_DATABASE'] = '1';
        } elseif (! $this->startTestDatabase()) {
            return self::FAILURE;
        }

        if ($this->option('update-snapshots')) {
            $env['UPDATE_SNAPSHOTS'] = '1';
        }

        $command = [PHP_BINARY, '-d', 'memory_limit=10G', base_path('vendor/bin/phpunit')];

        if ($suite !== 'full') {
            array_push($command, '--testsuite', $suite);
        }

        if ($filter = $this->option('filter')) {
            array_push($command, '--filter', $filter);
        }

        $this->info('Running the '.$suite.' suite: '.self::SUITES[$suite]);

        $process = new Process($command, base_path(), $env, null, null);
        $process->setTty(Process::isTtySupported());

        return $process->run(fn ($type, $output) => $this->output->write($output));
    }

    /** @return array<int, string> the variable names .env sets (never their values) */
    private function dotEnvKeys(): array
    {
        $path = base_path('.env');

        return is_file($path) ? array_keys(Dotenv::parse(file_get_contents($path))) : [];
    }

    /** Starts the test MySQL container (a no-op when it is already up). */
    private function startTestDatabase(): bool
    {
        $process = new Process(['docker', 'compose', '-f', self::COMPOSE_FILE, 'up', '-d', '--wait'], base_path(), null, null, 300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Could not start the test database container. Is Docker running?');
            $this->line(trim($process->getErrorOutput()));

            return false;
        }

        return true;
    }
}
