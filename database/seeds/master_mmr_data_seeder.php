<?php

use Illuminate\Database\Seeder;

class master_mmr_data_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-filesmaster_mmr_data.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
