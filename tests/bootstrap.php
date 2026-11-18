<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\CreatesApplication;

require __DIR__.'/../vendor/autoload.php';

// Migrate and seed the test container once; later runs reuse it (tests roll back their writes).
// Rebuild from scratch: docker compose -f docker-compose/docker-compose.testing.yaml down -v, then up -d.
$bootstrapper = new class
{
    use CreatesApplication;
};

$app = $bootstrapper::bootTestApplication();

// Unit tests don't touch the database (php artisan hp:test unit sets this)
if (getenv('TEST_SKIP_DATABASE')) {
    $app->flush();

    return;
}

// A freshly started container passes its healthcheck before the init scripts' restart is done
for ($attempt = 1; ; $attempt++) {
    try {
        DB::connection()->getPdo();
        break;
    } catch (Throwable $e) {
        if ($attempt === 30) {
            fwrite(STDERR, PHP_EOL.'Test database not reachable: '.$e->getMessage().PHP_EOL);
            exit(1);
        }
        DB::purge();
        sleep(2);
    }
}

$run = function (string $command, array $options = []) {
    fwrite(STDOUT, "Test database: {$command}...".PHP_EOL);
    $started = microtime(true);

    try {
        $exit = Artisan::call($command, $options + ['--force' => true]);
    } catch (Throwable $e) {
        fwrite(STDERR, PHP_EOL.$command.' failed: '.$e->getMessage().PHP_EOL.'Reset the container (down -v, up -d) once fixed.'.PHP_EOL);
        exit(1);
    }

    if ($exit !== 0) {
        fwrite(STDERR, Artisan::output());
        exit(1);
    }

    fwrite(STDOUT, sprintf('  done in %.0fs', microtime(true) - $started).PHP_EOL);
};

// Pending migrations only, so a new one lands without a rebuild
$run('migrate');

if (! Schema::hasTable('test_database_seeded')) {
    $run('db:seed');
    DB::statement('CREATE TABLE test_database_seeded (seeded_at DATETIME NOT NULL)');
    DB::table('test_database_seeded')->insert(['seeded_at' => now()]);
}

$app->flush();
