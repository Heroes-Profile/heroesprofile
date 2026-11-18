<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** players_list for the heroesprofile_ccl test slice (its latest 40 replays), exported from production. */
class PlayersListCCLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['players_list_id', 'full_battletag', 'battletag', 'blizz_id', 'region'];

        $data = [
            [29, 'Caesarsalad#1417', 'Caesarsalad', 54965, 1],
            [39, 'CPogX#1684', 'CPogX', 3697969, 1],
            [61, 'fancypants#1399', 'fancypants', 6121968, 1],
            [84, null, 'Hebi', 210869, 1],
            [102, 'Jun#14180', 'Jun', 993519, 1],
            [107, 'Kure#11538', 'Kure', 2090688, 1],
            [110, 'LegacY#12427', 'LegacY', 1273018, 1],
            [115, 'MadaraUchiha#1121', 'MadaraUchiha', 6826974, 1],
            [121, 'MasonBlaze#11574', 'MasonBlaze', 884179, 1],
            [126, 'Mokka#11906', 'Mokka', 541778, 1],
            [138, null, 'Nintorii', 4032739, 1],
            [185, 'TFNazmas#1319', 'TFNazmas', 340728, 1],
            [192, 'TScattle#1211', 'TScattle', 17161, 1],
            [195, 'Unaverted#11994', 'Unaverted', 551330, 1],
            [199, 'Vespertine#11697', 'Vespertine', 8874402, 1],
            [200, 'WearyDay#1911', 'WearyDay', 5535875, 1],
            [211, 'Zergling#1830', 'Zergling', 807262, 1],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_ccl')->table('players_list')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
