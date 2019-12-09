<?php

use Illuminate\Database\Seeder;

class global_hero_stats_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/SQL_Dumps/global_hero_stats.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
