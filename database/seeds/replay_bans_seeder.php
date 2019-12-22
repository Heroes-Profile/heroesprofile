<?php

use Illuminate\Database\Seeder;

class replay_bans_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/replay_bans.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
