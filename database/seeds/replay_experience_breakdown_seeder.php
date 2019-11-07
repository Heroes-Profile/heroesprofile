<?php

use Illuminate\Database\Seeder;

class replay_experience_breakdown_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/SQL_Dumps/replay_experience_breakdown.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
