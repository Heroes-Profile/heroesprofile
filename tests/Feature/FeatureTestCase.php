<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Runs against the seeded test container; every write is rolled back.
 *
 * A transaction starts on each connection the test actually uses, when it is first opened. Opening
 * all fifteen up front (as DatabaseTransactions does) burns through Windows' local ports over a run.
 */
abstract class FeatureTestCase extends TestCase
{
    /** @var array<int, string> */
    private array $transacting = [];

    /** Set once a test signs someone in; their guard has to survive between requests. */
    protected bool $keepsSignedInUser = false;

    public function be(Authenticatable $user, $guard = null)
    {
        $this->keepsSignedInUser = true;

        return parent::be($user, $guard);
    }

    /**
     * Each request gets fresh auth guards, as it does in production. The test app is shared
     * across a test's requests, so a stateless guard like api_key would otherwise keep
     * answering for the previous request (a worker call's Google token, say).
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        if (! $this->keepsSignedInUser) {
            $this->app['auth']->forgetGuards();
        }

        // Callbacks a request registers with app()->terminating() run when it ends. The test app outlives
        // the request, so without this the next request's terminate would run them a second time.
        $terminating = fn () => $this->terminatingCallbacks;
        $before = $terminating->call($this->app);

        try {
            return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        } finally {
            (fn () => $this->terminatingCallbacks = $before)->call($this->app);
        }
    }

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->transacting = [];

            Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
                $event->connection->unsetEventDispatcher();
                $event->connection->beginTransaction();
                $event->connection->setEventDispatcher($this->app['events']);
                $this->transacting[] = $event->connectionName;
            });

            $this->beforeApplicationDestroyed(function () {
                foreach (array_unique($this->transacting) as $name) {
                    $connection = DB::connection($name);
                    $connection->unsetEventDispatcher();
                    $connection->rollBack();
                    $connection->disconnect();
                }
            });
        });

        parent::setUp();
    }
}
