<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** teams for the heroesprofile_ccl test slice (its latest 40 replays), exported from production. */
class TeamsCCLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['team_id', 'season', 'team_name', 'image'];

        $data = [
            [25, 4, '30k', '30k.png'],
            [26, 4, 'Chilly Mountain', 'chilly_mountain.png'],
            [27, 4, 'Diamond Hands', 'diamond_hands.png'],
            [28, 4, 'Granit Gaming', 'Granit_Gaming.png'],
            [29, 4, 'Oxygen Esports', 'Oxygen_Esports.png'],
            [30, 4, 'Simplicity', 'Simplicity.png'],
            [31, 4, 'STORM Esports', 'storm_esports.png'],
            [32, 4, 'WildHeart Esports', 'WildHeart_Esports.png'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_ccl')->table('teams')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
