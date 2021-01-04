<?php

namespace Tests\Unit;

use Heroes\Factories\HeroFactory;
use Tests\TestCase;

/**
 * Images Test
 *
 * Ensures that all gamedata images needed
 * in the project are present (usually created
 * by images.sh form heroes-images).
 */
class ImagesTest extends TestCase
{
	/**
	 * Path to the images
	 *
	 * @var string|null
	 */
	private $path;

	/**
	 * @var HeroFactory|null
	 */
	private $heroes;

	protected function setUp(): void
	{
		parent::setUp();

		if (is_null($this->path))
		{
			$this->path   = public_path('images/heroesimages');
			$this->heroes = new HeroFactory();
		}
	}

	/**
	 * Ensures the directory and all its subfolders
	 * exist and are readable. Required for other tests.
	 *
	 * @dataProvider subfolderProvider
	 */
	public function testPaths(string $subfolder)
	{
		$this->assertDirectoryExists($this->path . DIRECTORY_SEPARATOR . $subfolder);
		$this->assertDirectoryIsReadable($this->path . DIRECTORY_SEPARATOR . $subfolder);
	}

	public function subfolderProvider()
	{
		return [
			[''],
			['abilitytalents'],
			['heroportraits'],
		];
	}

	/**
	 * @depends testPaths
	 */
	public function testHeroPortraits()
	{
		$subfolder = DIRECTORY_SEPARATOR . 'heroportraits' . DIRECTORY_SEPARATOR;

		foreach ($this->heroes as $hero)
		{
			foreach (['draftScreen', 'minimap', 'target'] as $portrait)
			{
				$image = $this->path . $subfolder . $hero->portraits->$portrait;

				$this->assertFileExists($image);
			}
		}		
	}

	/**
	 * @depends testPaths
	 */
	public function testSkillIcons()
	{
		$subfolder = DIRECTORY_SEPARATOR . 'abilitytalents' . DIRECTORY_SEPARATOR;

		foreach ($this->heroes as $hero)
		{
			foreach ($hero->abilities() as $ability)
			{
				$image = $this->path . $subfolder . $ability->icon;

				$this->assertFileExists($image);
			}

			foreach ($hero->talents() as $talent)
			{
				$image = $this->path . $subfolder . $talent->icon;

				$this->assertFileExists($image);
			}
		}		
	}
}
