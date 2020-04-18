<?php

use Illuminate\Database\Seeder;

class league_breakdowns_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/league_breakdowns.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
