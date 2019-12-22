<?php

use Illuminate\Database\Seeder;

class mmr_type_ids_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/mmr_type_ids.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
