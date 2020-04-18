<?php

use Illuminate\Database\Seeder;

class master_games_played_data_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/master_games_played_data.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
