<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** series for the heroesprofile_ml test slice (its latest 40 replays), exported from production. */
class SeriesMLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['series_id', 'name', 'icon'];

        $data = [
            [1, 'Agon League', '600px-Agonnewlogo2.png'],
            [2, 'All Hallows Cup', 'All_Hallows_Cup_banner.png'],
            [3, 'Bloodlust', 'Bloodlust-big.png'],
            [4, 'Brazilian Championship', 'Flag_of_Brazil.svg.png'],
            [5, 'DreamHack', '600px-DreamHack.png'],
            [6, 'Gold Series', 'GPL_logo_heroes.png'],
            [7, 'Heroes Lounge', null],
            [8, 'HeroesHype', 'hh_logo_large.png'],
            [9, 'HGC', 'HGC_logo.png'],
            [10, 'King of the Storm', '600px-King_of_the_Storm_Logo.jpg'],
            [11, 'Machines of War Showdown', 'machines_of_war.png'],
            [12, 'Nexus Contest', 'Nexus_Contest_banner.jpg'],
            [13, 'Nexus Games', '600px-Nexus_Games_logo.jpg'],
            [14, 'Nut Cup', null],
            [15, 'Toasty and Gladman', 'File_Toasty_&_Gladman.jpg'],
            [16, 'ZOTAC Cup', '141201_ZOTAC_HEROESARENA_END.png'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_esports_other')->table('series')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
