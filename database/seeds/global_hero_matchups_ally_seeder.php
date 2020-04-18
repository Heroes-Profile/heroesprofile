<?php

use Illuminate\Database\Seeder;

class global_hero_matchups_ally_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/global_hero_matchups_ally.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
