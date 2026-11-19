<?php

namespace Tests;

use App\Services\CloudTasksDispatcher;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** A week after the seed data's newest game (2024-02-06), in season 26: the site's "current" season and patch are ones the seeders fill. */
    public const FROZEN_NOW = '2024-02-10 12:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::FROZEN_NOW, 'UTC'));
        Http::preventStrayRequests();
        $this->withoutVite();
        $this->spy(CloudTasksDispatcher::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
