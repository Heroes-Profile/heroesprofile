<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** teams for the heroesprofile_mcl test slice (its latest 40 replays), exported from production. */
class TeamsMCLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['team_id', 'season', 'team_name', 'image'];

        $data = [
            [1, 1, '30k', '30k_logo.png'],
            [2, 1, 'Chilly Mountain', 'Chilly_Mountain_logo.png'],
            [3, 1, 'The Donuts', 'Big_Donut_logo.png'],
            [4, 1, 'The Hardos', 'The_Hardos_logo.png'],
            [5, 1, 'Soundless', 'Soundless_logo.png'],
            [6, 1, 'Go Next', 'Go_Next_logo2019.png'],
            [7, 1, 'Inting for Ruby', 'Inting_For_Ruby_logo.png'],
            [8, 1, 'ArthasStroitHram', 'ArthasStroitHram-logo_last_version.png'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_mcl')->table('teams')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
