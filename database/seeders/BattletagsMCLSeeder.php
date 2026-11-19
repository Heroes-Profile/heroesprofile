<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** battletags for the heroesprofile_mcl test slice (its latest 40 replays), exported from production. */
class BattletagsMCLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['player_id', 'team_id', 'blizz_id', 'battletag', 'region'];

        $data = [
            [29, null, 6509, 'Skynox#21979', 2],
            [24, null, 7152, 'TLHasuObs30K#2287', 2],
            [27, null, 82366, 'SNGzwHydra#2318', 2],
            [2, null, 129703, 'MindHawk#21616', 2],
            [35, null, 151488, 'SirIceCream#21776', 2],
            [26, null, 281528, 'Wit#21875', 2],
            [34, null, 316493, 'bLaDe#24712', 2],
            [21, null, 400190, 'Cris#22726', 2],
            [11, null, 415644, 'CMMakke#2521', 2],
            [15, null, 556249, 'XraYY30K#2901', 2],
            [43, null, 759925, 'GGYasu#2270', 2],
            [40, null, 759942, 'GGSkogh#2518', 2],
            [4, null, 780034, 'Hat#2720', 2],
            [38, null, 848625, 'GGQopen#2517', 2],
            [52, null, 881315, 'ukkieLFT#2575', 2],
            [22, null, 1107766, 'Nic#22170', 2],
            [36, null, 1234969, 'CCLauber#2477', 2],
            [10, null, 1296693, 'Denardo#2213', 2],
            [32, null, 1620193, 'Nenopi#21593', 2],
            [31, null, 1748782, 'Shamzik#2265', 2],
            [16, null, 1842569, 'Ultralisk30k#2243', 2],
            [54, null, 1926480, 'Rennella#2937', 2],
            [23, null, 2410444, 'METBadBenny#2947', 2],
            [20, null, 2513365, 'OXGBananaH#2399', 2],
            [3, null, 2639507, 'Fish777#2191', 2],
            [28, null, 2676617, 'FarlendeR#21889', 2],
            [5, null, 2720821, 'BabyHoudini#2575', 2],
            [1, null, 6328325, 'Knox#21885', 2],
            [39, null, 6457381, 'Iksir#21318', 2],
            [14, null, 6587585, 'CMberghult#2636', 2],
            [17, null, 7352198, 'Daykwaza30K#2506', 2],
            [6, null, 8986386, 'Картошечка#21708', 2],
            [8, null, 9291467, 'CkiLL9l#21303', 2],
            [9, null, 9638281, 'Муренас#21740', 2],
            [12, null, 9713101, 'CMJia#2744', 2],
            [37, null, 9738890, 'CCRutzou#2309', 2],
            [50, null, 9738890, 'OXGRutzou#2143', 2],
            [7, null, 10163333, 'Bishepss#2832', 2],
            [51, null, 10215876, 'Hans#23202', 2],
            [44, null, 10820208, 'Siro2#2141', 2],
            [13, null, 11118558, 'Azyrite30K#2162', 2],
            [49, null, 11233275, 'GNChelvin#2606', 2],
            [19, null, 12185076, 'Dynouh30K#2623', 2],
            [18, null, 12196892, 'Mascarade30K#2666', 2],
            [53, null, 13234427, 'KotJAM#2357', 2],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_mcl')->table('battletags')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
