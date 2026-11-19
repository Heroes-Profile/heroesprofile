<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** regions for the heroesprofile_ml test slice (its latest 40 replays), exported from production. */
class RegionsMLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['region_id', 'short_name', 'name'];

        $data = [
            [1, 'NA', 'North America'],
            [2, 'EU', 'Europe'],
            [3, 'KR', 'Korea'],
            [5, 'CN', 'China'],
            [6, 'ANZ', 'Australia and New Zealand'],
            [7, 'BR', 'Brazil'],
            [8, 'LATAM', 'Latin America'],
            [9, 'RU', 'Russia'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_esports_other')->table('regions')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
