<?php

use Illuminate\Database\Seeder;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$this->call([
			// For now use the same seeder as dev
			DatabaseSeeder::class,
		]);
    }
}
