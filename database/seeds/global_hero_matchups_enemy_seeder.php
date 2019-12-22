<?php

use Illuminate\Database\Seeder;

class global_hero_matchups_enemy_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = base_path('database/seeds/heroesprofile-seeds/seed-filesglobal_hero_matchups_enemy.sql');
      DB::unprepared(file_get_contents($sql));
    }
}
