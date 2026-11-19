<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** replay for the heroesprofile_ngs test slice (its latest 40 replays), exported from production. */
class ReplayNGSSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['replayID', 'tournament', 'season', 'division_0', 'division_1', 'team_0_name', 'team_1_name', 'round', 'game', 'first_pick', 'game_date', 'game_length', 'game_map', 'team_0_map_ban', 'team_0_map_ban_2', 'team_1_map_ban', 'team_1_map_ban_2', 'game_version', 'region', 'date_added'];

        $data = [
            [4781, 'NGS', '9', 'A East', 'A East', 'ParsleySnagRosemary&Line', 'Team Happy Cloud', 'T-3', 5, 1, '2020-07-20 01:33:45', 1019, 12, '10', '15', '14', '9', '1.2.51.1', 1, '2020-07-21 09:50:52'],
            [4780, 'NGS', '9', 'A East', 'A East', 'ParsleySnagRosemary&Line', 'Team Happy Cloud', 'T-3', 4, 1, '2020-07-20 01:03:45', 1032, 5, '10', '15', '14', '9', '1.2.51.1', 1, '2020-07-21 09:50:49'],
            [4779, 'NGS', '9', 'A East', 'A East', 'ParsleySnagRosemary&Line', 'Team Happy Cloud', 'T-3', 3, 0, '2020-07-20 00:36:53', 1153, 4, '10', '15', '14', '9', '1.2.51.1', 1, '2020-07-21 09:50:46'],
            [4778, 'NGS', '9', 'A East', 'A East', 'ParsleySnagRosemary&Line', 'Team Happy Cloud', 'T-3', 2, 1, '2020-07-20 00:05:22', 1080, 11, '10', '15', '14', '9', '1.2.51.1', 1, '2020-07-21 09:50:43'],
            [4777, 'NGS', '9', 'A East', 'A East', 'ParsleySnagRosemary&Line', 'Team Happy Cloud', 'T-3', 1, 1, '2020-07-19 23:37:16', 1481, 3, '10', '15', '14', '9', '1.2.51.1', 1, '2020-07-21 09:50:40'],
            [4776, 'NGS', '9', 'A West', 'A West', 'T@inted Orphans', 'ReGen Retro', 'T-2', 4, 1, '2020-07-18 05:00:29', 1078, 12, '3', '10', '11', '15', '1.2.51.1', 1, '2020-07-20 09:50:49'],
            [4775, 'NGS', '9', 'A West', 'A West', 'T@inted Orphans', 'ReGen Retro', 'T-2', 3, 0, '2020-07-18 04:32:36', 1197, 9, '3', '10', '11', '15', '1.2.51.1', 1, '2020-07-20 09:50:46'],
            [4774, 'NGS', '9', 'A West', 'A West', 'T@inted Orphans', 'ReGen Retro', 'T-2', 2, 1, '2020-07-18 04:00:05', 1382, 4, '3', '10', '11', '15', '1.2.51.1', 1, '2020-07-20 09:50:43'],
            [4773, 'NGS', '9', 'A West', 'A West', 'T@inted Orphans', 'ReGen Retro', 'T-2', 1, 0, '2020-07-18 03:26:52', 1183, 5, '3', '10', '11', '15', '1.2.51.1', 1, '2020-07-20 09:50:39'],
            [4772, 'NGS', '9', 'D West', 'D West', 'Team Rainbow Strike Go!', 'Artemis', 'T-2', 3, 0, '2020-07-16 04:04:28', 1018, 10, '11', '9', '5', '12', '1.2.51.1', 1, '2020-07-19 09:50:52'],
            [4771, 'NGS', '9', 'D West', 'D West', 'Team Rainbow Strike Go!', 'Artemis', 'T-2', 2, 0, '2020-07-16 03:33:22', 1332, 1, '11', '9', '5', '12', '1.2.51.1', 1, '2020-07-19 09:50:49'],
            [4770, 'NGS', '9', 'D West', 'D West', 'Team Rainbow Strike Go!', 'Artemis', 'T-2', 1, 0, '2020-07-16 02:59:27', 970, 4, '11', '9', '5', '12', '1.2.51.1', 1, '2020-07-19 09:50:46'],
            [4769, 'NGS', '9', 'B West', 'B West', 'Caffeinated Knights', 'sOaK eVeRy LaNe', 'T-2', 2, 1, '2020-07-14 04:27:56', 1131, 9, '11', '3', '12', '14', '1.2.51.0', 1, '2020-07-19 09:50:43'],
            [4768, 'NGS', '9', 'B West', 'B West', 'Caffeinated Knights', 'sOaK eVeRy LaNe', 'T-2', 1, 0, '2020-07-14 03:57:21', 1224, 15, '11', '3', '12', '14', '1.2.51.0', 1, '2020-07-19 09:50:40'],
            [4767, 'NGS', '9', 'Heroic', 'Heroic', 'Quaranteam', 'CPogX Fanclub', 'T-3', 5, 0, '2020-07-17 04:03:42', 1243, 10, '3', '11', '1', '15', '1.2.51.1', 1, '2020-07-18 09:51:01'],
            [4766, 'NGS', '9', 'Heroic', 'Heroic', 'Quaranteam', 'CPogX Fanclub', 'T-3', 4, 0, '2020-07-17 03:16:34', 1012, 4, '3', '11', '1', '15', '1.2.51.1', 1, '2020-07-18 09:50:58'],
            [4765, 'NGS', '9', 'Heroic', 'Heroic', 'Quaranteam', 'CPogX Fanclub', 'T-3', 3, 0, '2020-07-17 02:47:08', 1081, 5, '3', '11', '1', '15', '1.2.51.1', 1, '2020-07-18 09:50:55'],
            [4764, 'NGS', '9', 'Heroic', 'Heroic', 'Quaranteam', 'CPogX Fanclub', 'T-3', 2, 1, '2020-07-17 02:13:30', 1312, 12, '3', '11', '1', '15', '1.2.51.1', 1, '2020-07-18 09:50:52'],
            [4763, 'NGS', '9', 'Heroic', 'Heroic', 'Quaranteam', 'CPogX Fanclub', 'T-3', 1, 1, '2020-07-17 01:40:30', 1625, 9, '3', '11', '1', '15', '1.2.51.1', 1, '2020-07-18 09:50:48'],
            [4762, 'NGS', '9', 'D East', 'D East', 'Bunker Fun Time', 'Your Math Teachers', 'T-3', 3, 1, '2020-07-16 02:24:47', 1000, 15, '14', '9', '1', '11', '1.2.51.1', 1, '2020-07-18 09:50:44'],
            [4761, 'NGS', '9', 'D East', 'D East', 'Bunker Fun Time', 'Your Math Teachers', 'T-3', 2, 1, '2020-07-16 01:52:02', 850, 12, '14', '9', '1', '11', '1.2.51.1', 1, '2020-07-18 09:50:42'],
            [4760, 'NGS', '9', 'D East', 'D East', 'Bunker Fun Time', 'Your Math Teachers', 'T-3', 1, 0, '2020-07-16 01:24:57', 763, 4, '14', '9', '1', '11', '1.2.51.1', 1, '2020-07-18 09:50:39'],
            [4759, 'NGS', '9', 'B East', 'B East', 'Tiny Dancers', 'Die for Vision', 'T-3', 5, 0, '2020-07-17 02:17:46', 868, 4, '1', '11', '14', '5', '1.2.51.1', 1, '2020-07-17 09:50:49'],
            [4758, 'NGS', '9', 'B East', 'B East', 'Tiny Dancers', 'Die for Vision', 'T-3', 4, 0, '2020-07-17 01:51:10', 735, 10, '1', '11', '14', '5', '1.2.51.1', 1, '2020-07-17 09:50:47'],
            [4757, 'NGS', '9', 'B East', 'B East', 'Tiny Dancers', 'Die for Vision', 'T-3', 3, 1, '2020-07-17 01:27:24', 1456, 15, '1', '11', '14', '5', '1.2.51.1', 1, '2020-07-17 09:50:45'],
            [4756, 'NGS', '9', 'B East', 'B East', 'Tiny Dancers', 'Die for Vision', 'T-3', 2, 0, '2020-07-17 00:51:18', 922, 12, '1', '11', '14', '5', '1.2.51.1', 1, '2020-07-17 09:50:41'],
            [4755, 'NGS', '9', 'B East', 'B East', 'Tiny Dancers', 'Die for Vision', 'T-3', 1, 0, '2020-07-17 00:24:59', 965, 3, '1', '11', '14', '5', '1.2.51.1', 1, '2020-07-17 09:50:39'],
            [4754, 'NGS', '9', 'C West', 'C West', 'COSMOS', 'Amateur Opponents', 'T-3', 4, 0, '2020-07-15 03:39:24', 1162, 3, '10', '15', '1', '4', '1.2.51.0', 1, '2020-07-15 09:50:44'],
            [4753, 'NGS', '9', 'C West', 'C West', 'COSMOS', 'Amateur Opponents', 'T-3', 3, 0, '2020-07-15 03:05:30', 1791, 14, '10', '15', '1', '4', '1.2.51.0', 1, '2020-07-15 09:50:41'],
            [4752, 'NGS', '9', 'C West', 'C West', 'COSMOS', 'Amateur Opponents', 'T-3', 2, 0, '2020-07-15 02:25:38', 978, 5, '10', '15', '1', '4', '1.2.51.0', 1, '2020-07-15 09:50:37'],
            [4751, 'NGS', '9', 'C West', 'C West', 'COSMOS', 'Amateur Opponents', 'T-3', 1, 0, '2020-07-15 02:00:07', 1304, 11, '10', '15', '1', '4', '1.2.51.0', 1, '2020-07-15 09:50:34'],
            [4750, 'NGS', '9', 'Heroic', 'Heroic', 'We Take These', 'Quaranteam', 'T-2', 5, 0, '2020-07-10 05:48:29', 904, 10, '14', '15', '11', '5', '1.2.51.0', 1, '2020-07-15 09:50:31'],
            [4749, 'NGS', '9', 'B West', 'B West', 'Aba Murky Cult', 'Arrogant Nephalem', 'T-2', 2, 1, '2020-07-14 04:58:28', 1088, 11, '12', '10', '1', '4', '1.2.51.0', 1, '2020-07-14 09:51:03'],
            [4748, 'NGS', '9', 'B West', 'B West', 'Aba Murky Cult', 'Arrogant Nephalem', 'T-2', 1, 0, '2020-07-14 04:29:11', 829, 15, '12', '10', '1', '4', '1.2.51.0', 1, '2020-07-14 09:51:00'],
            [4747, 'NGS', '9', 'A East', 'A East', 'Sky5', 'Team Happy Cloud', 'T-2', 2, 0, '2020-07-13 02:27:04', 1390, 11, '10', '3', '12', '9', '1.2.51.0', 1, '2020-07-14 09:50:57'],
            [4746, 'NGS', '9', 'A East', 'A East', 'Sky5', 'Team Happy Cloud', 'T-2', 1, 0, '2020-07-13 01:51:25', 1907, 15, '10', '3', '12', '9', '1.2.51.0', 1, '2020-07-14 09:50:53'],
            [4745, 'NGS', '9', 'Heroic', 'Heroic', 'CPogX Fanclub', 'Press Forward', 'T-2', 3, 1, '2020-07-10 03:53:42', 871, 5, '1', '9', '3', '14', '1.2.51.0', 1, '2020-07-14 09:50:50'],
            [4744, 'NGS', '9', 'Heroic', 'Heroic', 'CPogX Fanclub', 'Press Forward', 'T-2', 2, 1, '2020-07-10 03:27:25', 778, 4, '1', '9', '3', '14', '1.2.51.0', 1, '2020-07-14 09:50:47'],
            [4743, 'NGS', '9', 'Heroic', 'Heroic', 'CPogX Fanclub', 'Press Forward', 'T-2', 1, 1, '2020-07-10 03:03:50', 1545, 12, '1', '9', '3', '14', '1.2.51.0', 1, '2020-07-14 09:50:45'],
            [4742, 'NGS', '9', 'Heroic', 'Heroic', 'We Take These', 'Quaranteam', 'T-2', 4, 1, '2020-07-10 05:22:12', 1558, 4, '14', '15', '11', '5', '1.2.51.0', 1, '2020-07-14 09:50:40'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_ngs')->table('replay')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
