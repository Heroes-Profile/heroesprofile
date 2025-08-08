<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatchPredictionSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['1', '1', '2024-06-04 09:20:00', '2024-10-14 00:00:00'],
            ['2', '2', '2024-10-14 00:00:01', '2025-08-08 00:00:00'],
            ['3', '3', '2025-08-08 00:00:01', null],
        ];

        foreach ($data as $row) {
            DB::table('match_prediction_season')->insert([
                'match_prediction_season_id' => $row[0],
                'season' => $row[1],
                'start_date' => $row[2],
                'end_date' => $row[3],
            ]);
        }
    }
}
