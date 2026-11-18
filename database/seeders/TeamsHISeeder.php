<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** teams for the heroesprofile_hi test slice (its latest 40 replays), exported from production. */
class TeamsHISeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['team_id', 'season', 'team_name', 'image'];

        $data = [
            [1, 1, '30k', '30k'],
            [2, 1, '5 Shots Of Copium', '5_Shots_Of_Copium'],
            [3, 1, 'Ammonite', 'Ammonite'],
            [4, 1, 'Bang Bush', 'Bang_Bush'],
            [5, 1, 'Diamond Hands', 'Diamond_Hands'],
            [6, 1, 'Performance Enhancing Zugs', 'Performance_Enhancing_Zugs'],
            [7, 1, 'Tempest', 'Tempest'],
            [8, 1, 'Washed Up', 'Washed_Up'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_hi')->table('teams')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
