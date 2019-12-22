<?php

use Illuminate\Database\Seeder;

class league_tiers_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/league_tiers.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
