<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['-1', 'Brawl', 'Brawl', 'br'],
            ['0', 'Custom', 'Custom', 'cu'],
            ['1', 'Quick Match', 'QuickMatch', 'qm'],
            ['2', 'Unranked Draft', 'UnrankedDraft', 'ud'],
            ['3', 'Hero League', 'HeroLeague', 'hl'],
            ['4', 'Team League', 'TeamLeague', 'tl'],
            ['5', 'Storm League', 'StormLeague', 'sl'],
            ['6', 'ARAM', 'ARAM', 'ar'],
        ];

        foreach ($data as $row) {
            DB::table('game_types')->insert([
                'type_id' => $row[0],
                'name' => $row[1],
                'no_space_name' => $row[2],
                'short_name' => $row[3],
            ]);
        }
    }
}
