<?php

use Illuminate\Database\Seeder;

class global_hero_talents_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/global_hero_talents.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
