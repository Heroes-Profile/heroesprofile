<?php

use Illuminate\Database\Seeder;

class season_game_versions_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/season_game_versions.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
