<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Patch 2.55.12.94786's season_game_versions row, exported from production. */
class SeasonGameVersionsPatch94786Seeder extends Seeder
{
    public function run(): void
    {
        $columns = ['id', 'season', 'game_version', 'date_added', 'valid_globals', 'patch_notes_url'];

        $data = [
            [248, 30, '2.55.12.94786', '2025-08-04 19:29:22', 1, null],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile')->table('season_game_versions')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
