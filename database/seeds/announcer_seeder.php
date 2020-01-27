<?php

use Illuminate\Database\Seeder;

class announcer_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/announcers.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
