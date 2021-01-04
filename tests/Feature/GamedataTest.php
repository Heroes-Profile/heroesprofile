<?php

namespace Tests\Feature;

use Heroes\Locator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DatabaseTestCase;

class GamedataTest extends DatabaseTestCase
{
	public function testDefaultHeroes()
	{
		$response = $this->get('/Gamedata');

		$response
			->assertStatus(200)
			->assertSeeText('Hero Data');
	}

	public function testForm()
	{
		$response = $this->get('/Gamedata/Heroes');

		$response
			->assertStatus(200)
			// Patches
			->assertSeeTextInOrder([
				'2.47.2.76003',
				'2.53.0.83077',
			])
			// Locales
			->assertSeeTextInOrder([
				'Germany',
				'USA',
				'China',
			]);
	}

	public function testHeroes()
	{
		$response = $this->get('/Gamedata/Heroes');

		$response
			->assertStatus(200)
			->assertSeeTextInOrder([
				'Brightwing',
				'Qhira',
				'WitchDoctor',
			]);
	}

	public function testHero()
	{
		$response = $this->get('/Gamedata/Heroes/Abathur');

		$response
			->assertStatus(200)
			// Abilities
			->assertSeeTextInOrder([
				'Symbiote',
				'Locust Strain',
				'Stab',
			])
			// Levels
			->assertSeeTextInOrder([
				'Level 1',
				'Level 4',
				'Level 7',
				'Level 10',
				'Level 13',
				'Level 16',
				'Level 20',
			])
			// Talents
			->assertSeeTextInOrder([
				'Pressurized Glands',
				'Networked Carapace',
				'Volatile Mutation',
			]);
	}

	public function testHeroLocale()
	{
		$response = $this->get('/Gamedata/Heroes/Abathur?locale=frfr');

		$response
			->assertStatus(200)
			->assertSeeText('le maître des évolutions');
	}

	public function testHeroPatch()
	{
		$response = $this->get('/Gamedata/Heroes/Tracer?patch=2.48.0.76389');

		$response
			->assertStatus(200)
			->assertSeeText('Pulse Strike')
			->assertDontSeeText('One-Two Punch');
	}

	public function testHeroMissing()
	{
		$response = $this->get('/Gamedata/Heroes/Hogger?patch=2.48.0.76389');

		$response
			->assertStatus(302)
			->assertSessionHas('status', 'Unable to locate hero "Hogger" in patch 2.48.0.76389');
	}
}
