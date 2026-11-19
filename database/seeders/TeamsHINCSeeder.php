<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** teams for the heroesprofile_hi_nc test slice (its latest 40 replays), exported from production. */
class TeamsHINCSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['team_id', 'season', 'team_name', 'image'];

        $data = [
            [1, 1, 'Austria', 'Austria'],
            [2, 1, 'Britannia Rules The Lanes', 'Great Britain'],
            [3, 1, 'Bulgaria', 'Bulgaria'],
            [4, 1, 'Česká republika', 'Czechia'],
            [5, 1, 'Czechia', 'Czechia'],
            [6, 1, 'Denmark', 'Denmark'],
            [7, 1, 'Finland', 'Finland'],
            [8, 1, 'France', 'France'],
            [9, 1, 'Germany', 'Germany'],
            [10, 1, 'Glad vikings', 'Norway'],
            [11, 1, 'Hungary', 'Hungary'],
            [12, 1, 'In å veva', 'Sweden'],
            [13, 1, 'Les saucissonneurs', 'France'],
            [14, 1, 'Mad Vikings', 'Denmark'],
            [15, 1, 'Motståndarlaget', 'Sweden'],
            [16, 1, 'Nederland', 'Netherland'],
            [17, 1, 'Norway', 'Norway'],
            [18, 1, 'Oettinger Allstars', 'Germany'],
            [19, 1, 'Poland', 'Poland'],
            [20, 1, 'RossPierrDoll', 'Poland'],
            [21, 1, 'Russian Players', 'White Flag'],
            [22, 1, 'SomethingNew', 'France'],
            [23, 1, 'Spain', 'Spain'],
            [24, 1, 'Sweden', 'Sweden'],
            [25, 1, 'Ukraine', 'Ukraine'],
            [26, 1, 'United Kingdom', 'Great britain'],
            [27, 1, 'Zermettrlinge', 'Germany'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_hi_nc')->table('teams')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
