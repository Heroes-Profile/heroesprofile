<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonGameVersionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * major, minor, patch, and build are generated columns — do not insert them.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            // id, season, game_version, date_added, valid_globals, patch_notes_url
            [231, 25, '2.55.4.91418', '2023-11-21 18:24:07', 1, null],
        ];

        foreach ($data as $row) {
            DB::table('season_game_versions')->updateOrInsert(
                [
                    'season' => $row[1],
                    'game_version' => $row[2],
                ],
                [
                    'id' => $row[0],
                    'date_added' => $row[3],
                    'valid_globals' => $row[4],
                    'patch_notes_url' => $row[5],
                ]
            );
        }
    }
}
