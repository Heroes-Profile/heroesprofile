<?php

use Illuminate\Database\Seeder;

class ban_combinations_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('heroesprofile.ban_combinations')->insert();
    }
}
