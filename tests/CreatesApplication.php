<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public const TEST_DB_PORT = 33061;

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        return static::bootTestApplication();
    }

    public static function bootTestApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        static::assertLocalTestDatabase($app);

        return $app;
    }

    /** The local .env points at production-like servers, so nothing runs unless every connection is the test container. */
    public static function assertLocalTestDatabase(Application $app): void
    {
        if ($app->configurationIsCached()) {
            static::abortTests('bootstrap/cache/config.php exists; cached config ignores .env.testing. Run php artisan config:clear.');
        }

        if ($app->environment() !== 'testing') {
            static::abortTests('APP_ENV is "'.$app->environment().'", expected "testing".');
        }

        foreach (config('database.connections') as $name => $connection) {
            if (($connection['driver'] ?? null) !== 'mysql') {
                continue;
            }

            $host = $connection['host'] ?? null;
            $port = (int) ($connection['port'] ?? 0);

            if (! in_array($host, ['127.0.0.1', 'localhost'], true) || $port !== self::TEST_DB_PORT) {
                static::abortTests("Connection {$name} points at {$host}:{$port}, not the local test container (127.0.0.1:".self::TEST_DB_PORT.').');
            }
        }
    }

    private static function abortTests(string $message): never
    {
        fwrite(STDERR, PHP_EOL.'Refusing to run tests: '.$message.PHP_EOL);
        exit(1);
    }
}
