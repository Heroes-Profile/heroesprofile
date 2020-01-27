<?php

use Illuminate\Database\Seeder;

class voice_lines_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/voice_lines.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
