<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** replay for the heroesprofile_ml test slice (its latest 40 replays), exported from production. */
class ReplayMLSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['replayID', 'series', 'region', 'tournament', 'season', 'team_0_name', 'team_1_name', 'round', 'game', 'first_pick', 'game_date', 'game_length', 'game_map', 'game_version', 'date_added'];

        $data = [
            [6950, 'HGC', 0, '2018 Mid-Season Brawl', '2018', 'Team Dignitas', 'Tempest', 20180609, 2, 0, '2018-06-09 16:17:35', 2081, 1, '2.33.1.65654', '2025-02-09 21:04:21'],
            [6949, 'HGC', 1, 'North America Summer Regional 2', '2016', 'COGnitive Gaming', 'Panda Global', 20160424, 2, -1, '2016-04-24 23:38:44', 1091, 10, '0.17.3.42406', '2025-02-09 13:47:15'],
            [6947, 'HGC', 1, 'HGC North America - Open Division', '2017', 'Imported Support', 'Donkeys R Us', 20170906, 2, 1, '2017-09-06 03:04:39', 899, 12, '2.27.2.56705', '2025-02-09 13:46:16'],
            [6946, 'HGC', 1, '2018 HGC North America - Phase 2', '2018', 'No Tomorrow', 'PSISTORM Gaming', 20181006, 4, 1, '2018-10-06 21:29:28', 1093, 1, '2.37.1.68509', '2025-02-09 13:45:41'],
            [6945, 'HGC', 1, '2018 HGC NA - Phase 1', '2018', 'LFM Esports', 'HeroesHearth Esports', 20180421, 2, 1, '2018-04-21 22:07:41', 1325, 5, '2.31.3.64129', '2025-02-09 13:45:08'],
            [6944, 'HGC', 3, 'HGC Europe', '2017', 'Synergy', 'Team Zealot', 20170527, 4, 1, '2017-05-27 19:00:09', 720, 10, '2.25.4.53548', '2025-02-09 13:44:39'],
            [6943, 'HGC', 3, 'HGC Europe', '2017', 'Team Liquid', 'Team Expert', 20170506, 4, 0, '2017-05-06 21:57:57', 1339, 12, '2.25.1.52986', '2025-02-09 13:44:14'],
            [6942, 'HGC', 0, '2018 HGC Finals', '2018', 'Tempest', 'HeroesHearth Esports', 20181028, 1, 0, '2018-10-28 17:45:15', 919, 12, '2.39.0.69350', '2025-02-09 13:43:47'],
            [6941, 'HGC', 0, '2018 HGC Finals', '2018', 'Tempo Storm', 'Miracle', 20181027, 1, 0, '2018-10-27 17:50:15', 1291, 9, '2.39.0.69350', '2025-02-09 13:42:47'],
            [6940, 'HGC', 0, '2018 HGC Finals', '2018', 'HeroesHearth Esports', 'TheOne', 20181027, 1, 1, '2018-10-27 23:14:58', 1179, 5, '2.39.0.69350', '2025-02-09 13:42:19'],
            [6939, 'HGC', 2, 'Europe Fall Regional 2', '2016', 'GoogleIshetZontV2', 'Too far bjutifl for 640x480', 20160727, 1, -1, '2016-07-27 18:15:04', 783, 9, '0.19.2.44797', '2025-02-09 13:41:45'],
            [6936, 'HGC', 5, '2018 HGC China - Phase 2', '2018', 'RPG', 'KT', 20180917, 1, 0, '2018-09-17 12:18:29', 1197, 12, '2.37.0.67985', '2025-02-09 13:40:31'],
            [6935, 'HGC', 5, '2018 HGC China - Phase 2', '2018', 'Super Perfect Team', 'CE', 20180718, 2, 0, '2018-07-18 09:19:05', 1210, 12, '2.35.0.66488', '2025-02-09 13:40:01'],
            [6934, 'HGC', 5, '2018 HGC China - Phase 1', '2018', 'Beyond the Game', 'TheOne', 20180425, 3, 0, '2018-04-25 10:48:36', 1156, 3, '2.31.3.64129', '2025-02-09 13:39:56'],
            [6933, 'HGC', 5, '2018 HGC China - Phase 1', '2018', 'Beyond the Game', 'TheOne', 20180130, 1, 0, '2018-01-30 11:57:33', 960, 4, '2.29.9.61552', '2025-02-09 13:39:23'],
            [6932, 'HGC', 5, '2018 HGC China - Phase 1', '2018', 'Beyond the Game', 'TheOne', 20180130, 1, 0, '2018-01-30 11:57:33', 960, 4, '2.29.9.61552', '2025-02-09 13:20:44'],
            [6931, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'QM Warriors', 20180313, 3, 1, '2018-03-13 10:21:43', 1657, 1, '2.30.5.63070', '2025-02-09 06:25:22'],
            [6930, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'QM Warriors', 20180313, 1, 1, '2018-03-13 09:04:33', 1254, 3, '2.30.5.63070', '2025-02-09 06:25:19'],
            [6929, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Crimson Gaming', 'QM Warriors', 20180313, 1, 0, '2018-03-13 10:53:54', 893, 3, '2.30.5.63070', '2025-02-09 06:25:18'],
            [6928, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Crimson Gaming', 'QM Warriors', 20180313, 2, 0, '2018-03-13 11:24:06', 1035, 10, '2.30.5.63070', '2025-02-09 06:25:18'],
            [6927, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'QM Warriors', 20180313, 2, 1, '2018-03-13 09:36:20', 1046, 5, '2.30.5.63070', '2025-02-09 06:25:17'],
            [6926, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'Crimson Gaming', 20180315, 1, 1, '2018-03-15 09:05:14', 1395, 11, '2.30.5.63070', '2025-02-09 06:25:14'],
            [6925, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'Emprox', 20180313, 1, 0, '2018-03-13 08:52:12', 717, 9, '2.30.5.63070', '2025-02-09 06:25:13'],
            [6924, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'Crimson Gaming', 20180315, 2, 1, '2018-03-15 09:38:19', 1211, 5, '2.30.5.63070', '2025-02-09 06:25:11'],
            [6923, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'Emprox', 20180313, 2, 1, '2018-03-13 09:19:20', 830, 10, '2.30.5.63070', '2025-02-09 06:25:09'],
            [6922, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'Aztech Entertainment', 20180315, 1, 1, '2018-03-15 11:14:25', 1517, 14, '2.30.5.63070', '2025-02-09 06:25:09'],
            [6921, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'Aztech Entertainment', 20180315, 2, 1, '2018-03-15 11:50:35', 1447, 10, '2.30.5.63070', '2025-02-09 06:25:08'],
            [6920, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'Downfall Gaming', 20180315, 2, 0, '2018-03-15 10:41:13', 995, 14, '2.30.5.63070', '2025-02-09 06:25:07'],
            [6919, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'Downfall Gaming', 20180315, 1, 1, '2018-03-15 10:11:34', 906, 11, '2.30.5.63070', '2025-02-09 06:25:07'],
            [6918, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'Crimson Gaming', 20180320, 2, 1, '2018-03-20 10:44:11', 1216, 9, '2.30.5.63070', '2025-02-09 06:25:07'],
            [6917, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'Crimson Gaming', 20180320, 1, 1, '2018-03-20 10:12:32', 1235, 3, '2.30.5.63070', '2025-02-09 06:25:06'],
            [6916, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'Aztech Entertainment', 20180315, 3, 1, '2018-03-15 12:16:24', 926, 9, '2.30.5.63070', '2025-02-09 06:25:05'],
            [6915, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'QM Warriors', 20180320, 1, 1, '2018-03-20 10:02:56', 992, 5, '2.30.5.63070', '2025-02-09 06:25:00'],
            [6914, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'QM Warriors', 20180320, 2, 1, '2018-03-20 09:33:52', 1243, 9, '2.30.5.63070', '2025-02-09 06:24:59'],
            [6913, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Crimson Gaming', 'Aztech Entertainment', 20180322, 1, 1, '2018-03-22 09:01:40', 1285, 14, '2.30.6.63402', '2025-02-09 06:24:59'],
            [6912, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'QM Warriors', 20180320, 2, 0, '2018-03-20 10:24:28', 654, 1, '2.30.5.63070', '2025-02-09 06:24:57'],
            [6911, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Outlaws Gaming', 'QM Warriors', 20180320, 1, 1, '2018-03-20 08:56:54', 851, 10, '2.30.5.63070', '2025-02-09 06:24:57'],
            [6910, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Nomia', 'Aztech Entertainment', 20180322, 1, -1, '2018-03-22 10:18:06', 1218, 14, '2.30.6.63402', '2025-02-09 06:24:55'],
            [6909, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'Outlaws Gaming', 20180322, 1, 0, '2018-03-22 08:59:19', 1021, 3, '2.30.6.63402', '2025-02-09 06:24:55'],
            [6908, 'HGC', 6, '2018 HGC ANZ Premier Division - Phase 1', '2018', 'Downfall Gaming', 'QM Warriors', 20180403, 1, 0, '2018-04-03 11:09:18', 1584, 9, '2.31.1.63635', '2025-02-09 06:24:55'],
        ];

        foreach (array_chunk($data, 500) as $chunk) {
            DB::connection('heroesprofile_esports_other')->table('replay')->insert(array_map(fn ($row) => array_combine($columns, $row), $chunk));
        }
    }
}
