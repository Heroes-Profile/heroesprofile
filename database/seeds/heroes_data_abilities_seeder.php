<?php

use Illuminate\Database\Seeder;

class heroes_data_abilities_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-filesheroes_data_abilities.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
