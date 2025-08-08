<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonGameVersionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [231,25,'2.55.4.91418','2023-11-21 18:24:07',1],
        ];


        foreach ($data as $row) {
            DB::table('season_game_versions')->insert([
                'id' => $row[0],
                'season' => $row[1],
                'game_version' => $row[2],
                'date_added' => $row[3],
                'valid_globals' => $row[4],
            ]);
        }
    }
}
