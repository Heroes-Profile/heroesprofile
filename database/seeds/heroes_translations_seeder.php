<?php

use Illuminate\Database\Seeder;

class heroes_translations_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-files/heroes_translations.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
