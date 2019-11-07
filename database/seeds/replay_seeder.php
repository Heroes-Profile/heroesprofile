<?php

use Illuminate\Database\Seeder;

class replay_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/SQL_Dumps/replay.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
