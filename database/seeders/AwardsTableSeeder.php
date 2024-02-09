<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AwardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['award_id' => 1, 'title' => 'MVP', 'icon' => 'storm_ui_mvp_mvp'],
            ['award_id' => 2, 'title' => 'Dominator', 'icon' => 'storm_ui_mvp_dominator'],
            ['award_id' => 3, 'title' => 'Most XP Contribution', 'icon' => 'storm_ui_mvp_experienced'],
            ['award_id' => 4, 'title' => 'Painbringer', 'icon' => 'storm_ui_mvp_painbringer'],
            ['award_id' => 5, 'title' => 'Siegemaster', 'icon' => 'storm_ui_mvp_siegemaster'],
            ['award_id' => 6, 'title' => 'Bulwark', 'icon' => 'storm_ui_mvp_avenger'],
            ['award_id' => 7, 'title' => 'Main Healer', 'icon' => 'storm_ui_mvp_mainhealer'],
            ['award_id' => 8, 'title' => 'Stunner', 'icon' => 'storm_ui_mvp_stunner'],
            ['award_id' => 9, 'title' => 'Headhunter', 'icon' => 'storm_ui_mvp_headhunter'],
            ['award_id' => 10, 'title' => 'Most Kills', 'icon' => 'storm_ui_mvp_finisher'],
            ['award_id' => 11, 'title' => 'Hat Trick', 'icon' => 'storm_ui_mvp_hattrick'],
            ['award_id' => 12, 'title' => 'Clutch Healer', 'icon' => 'storm_ui_mvp_clutchhealer'],
            ['award_id' => 13, 'title' => 'Protector', 'icon' => 'storm_ui_mvp_protector'],
            ['award_id' => 14, 'title' => 'Sole Survivor', 'icon' => 'storm_ui_mvp_solesurvivor'],
            ['award_id' => 15, 'title' => 'Trapper', 'icon' => 'storm_ui_mvp_trapper'],
            ['award_id' => 16, 'title' => 'Team Player', 'icon' => 'storm_ui_mvp_teamplayer'],
            ['award_id' => 17, 'title' => 'Daredevil', 'icon' => 'storm_ui_mvp_daredevil'],
            ['award_id' => 18, 'title' => 'Escape Artist', 'icon' => 'storm_ui_mvp_escapeartist'],
            ['award_id' => 19, 'title' => 'Silencer', 'icon' => 'storm_ui_mvp_silencer'],
            ['award_id' => 20, 'title' => 'Most Teamfight Damage Taken', 'icon' => 'storm_ui_mvp_guardian'],
            ['award_id' => 21, 'title' => 'Teamfight Healing', 'icon' => 'storm_ui_mvp_combatmedic'],
            ['award_id' => 22, 'title' => 'Scrapper', 'icon' => 'storm_ui_mvp_scrapper'],
            ['award_id' => 23, 'title' => 'Avenger', 'icon' => 'storm_ui_mvp_avenger'],
            ['award_id' => 24, 'title' => 'Immortal Damage', 'icon' => 'storm_ui_mvp_immortalslayer'],
            ['award_id' => 25, 'title' => 'Money Bags', 'icon' => 'storm_ui_mvp_moneybags'],
            ['award_id' => 26, 'title' => 'Master of the Curse', 'icon' => 'storm_ui_mvp_masterofthecurse'],
            ['award_id' => 27, 'title' => 'Dragonshire Shrines Captured', 'icon' => 'storm_ui_mvp_shriner'],
            ['award_id' => 28, 'title' => 'Damage to Plants', 'icon' => 'storm_ui_mvp_guardianslayer'],
            ['award_id' => 29, 'title' => 'Garden Terror', 'icon' => 'storm_ui_mvp_gardenterror'],
            ['award_id' => 30, 'title' => 'Skull Collector', 'icon' => 'storm_ui_mvp_skullcollector'],
            ['award_id' => 31, 'title' => 'Guardian Slayer', 'icon' => 'storm_ui_mvp_guardianslayer'],
            ['award_id' => 32, 'title' => 'Temple Master', 'icon' => 'storm_ui_mvp_templemaster'],
            ['award_id' => 33, 'title' => 'Jeweler', 'icon' => 'storm_ui_mvp_jeweler'],
            ['award_id' => 34, 'title' => 'Most Altar Shots', 'icon' => 'storm_ui_mvp_cannoneer'],
            ['award_id' => 35, 'title' => 'Zerg Crusher', 'icon' => 'storm_ui_mvp_zergcrusher'],
            ['award_id' => 36, 'title' => 'Da Bomb', 'icon' => 'storm_ui_mvp_dabomb'],
            ['award_id' => 37, 'title' => 'Pusher', 'icon' => 'storm_ui_mvp_pusher'],
            ['award_id' => 38, 'title' => 'Pointguard', 'icon' => 'storm_ui_mvp_pointguard'],
            ['award_id' => 39, 'title' => 'Loyal Defender', 'icon' => 'storm_ui_mvp_loyaldefender'],
            ['award_id' => 40, 'title' => 'Seed Collector', 'icon' => 'storm_ui_mvp_gardenterror'],
        ];


        foreach ($data as $row) {
            DB::table('awards')->insert($row);
        }
    }
}
