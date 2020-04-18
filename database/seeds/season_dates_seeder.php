<?php

use Illuminate\Database\Seeder;

class season_dates_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/season_dates.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
