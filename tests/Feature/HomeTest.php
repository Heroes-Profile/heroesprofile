<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DatabaseTestCase;

class HomeTest extends DatabaseTestCase
{     

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSeeText('Global Stats');
    }
}
