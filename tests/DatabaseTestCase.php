<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Database\Seeders\TestingSeeder;

abstract class DatabaseTestCase extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Seed the database with the test seeder.
     *
     * @return void
     */
    public function setUp(): void
    {
    	parent::setup();

        $this->seed(TestingSeeder::class);
	}   
}
