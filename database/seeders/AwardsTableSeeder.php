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
            ['award_id' => 1, 'title' => 'MVP', 'icon' => 'storm_ui_mvp_mvp', 'description' => 'Highest overall performance score in the match.'],
            ['award_id' => 2, 'title' => 'Dominator', 'icon' => 'storm_ui_mvp_dominator', 'description' => 'Scored the most takedowns in one life.'],
            ['award_id' => 3, 'title' => 'Most XP Contribution', 'icon' => 'storm_ui_mvp_experienced', 'description' => 'Most experience earned for the team.'],
            ['award_id' => 4, 'title' => 'Painbringer', 'icon' => 'storm_ui_mvp_painbringer', 'description' => 'Most damage dealt to enemy heroes.'],
            ['award_id' => 5, 'title' => 'Siegemaster', 'icon' => 'storm_ui_mvp_siegemaster', 'description' => 'Most damage dealt to enemy structures and Minions.'],
            ['award_id' => 6, 'title' => 'Bulwark', 'icon' => 'storm_ui_mvp_avenger', 'description' => 'Most damage taken by a Warrior Hero.'],
            ['award_id' => 7, 'title' => 'Main Healer', 'icon' => 'storm_ui_mvp_mainhealer', 'description' => 'Most healing done to allied heroes.'],
            ['award_id' => 8, 'title' => 'Stunner', 'icon' => 'storm_ui_mvp_stunner', 'description' => 'Longest combined Stun time against enemy Heroes (including Sleep).'],
            ['award_id' => 9, 'title' => 'Headhunter', 'icon' => 'storm_ui_mvp_headhunter', 'description' => 'Most Mercenary camps captured.'],
            ['award_id' => 11, 'title' => 'Most Kills', 'icon' => 'storm_ui_mvp_finisher', 'description' => 'Most kills scored.'],
            ['award_id' => 12, 'title' => 'Hat Trick', 'icon' => 'storm_ui_mvp_hattrick', 'description' => 'Scored the first three kills of the game.'],
            ['award_id' => 13, 'title' => 'Clutch Healer', 'icon' => 'storm_ui_mvp_clutchhealer', 'description' => 'Most clutch heals (healed a Hero who would otherwise die).'],
            ['award_id' => 14, 'title' => 'Protector', 'icon' => 'storm_ui_mvp_protector', 'description' => 'Most damage prevented (e.g. using shields).'],
            ['award_id' => 15, 'title' => 'Sole Survivor', 'icon' => 'storm_ui_mvp_solesurvivor', 'description' => 'The only Hero with no deaths.'],
            ['award_id' => 16, 'title' => 'Trapper', 'icon' => 'storm_ui_mvp_trapper', 'description' => 'Longest combined Root time against enemy heroes.'],
            ['award_id' => 17, 'title' => 'Team Player', 'icon' => 'storm_ui_mvp_teamplayer', 'description' => 'Did not die while outnumbered by the enemy team.'],
            ['award_id' => 18, 'title' => 'Daredevil', 'icon' => 'storm_ui_mvp_daredevil', 'description' => 'Made the most escapes at critically low health during teamfights.'],
            ['award_id' => 19, 'title' => 'Escape Artist', 'icon' => 'storm_ui_mvp_escapeartist', 'description' => 'Made the most escapes at critically low health.'],
            ['award_id' => 20, 'title' => 'Silencer', 'icon' => 'storm_ui_mvp_silencer', 'description' => 'Longest combined Silence time against enemy heroes (including Polymorphs).'],
            ['award_id' => 21, 'title' => 'Most Teamfight Damage Taken', 'icon' => 'storm_ui_mvp_guardian', 'description' => 'Most team fight damage soaked.'],
            ['award_id' => 22, 'title' => 'Teamfight Healing', 'icon' => 'storm_ui_mvp_combatmedic', 'description' => 'Most team fight damage healed.'],
            ['award_id' => 23, 'title' => 'Scrapper', 'icon' => 'storm_ui_mvp_scrapper', 'description' => 'Most damage dealt in team fights.'],
            ['award_id' => 24, 'title' => 'Avenger', 'icon' => 'storm_ui_mvp_avenger', 'description' => 'Player with the most revenge kills (killed the Hero that last killed them).'],
            ['award_id' => 1001, 'title' => 'Immortal Damage', 'icon' => 'storm_ui_mvp_immortalslayer', 'description' => 'Most damage dealt to the enemy Immortal on Battlefield of Eternity.'],
            ['award_id' => 1002, 'title' => 'Money Bags', 'icon' => 'storm_ui_mvp_moneybags', 'description' => 'Most coins turned in on Blackheart\'s Bay.'],
            ['award_id' => 1003, 'title' => 'Master of the Curse', 'icon' => 'storm_ui_mvp_masterofthecurse', 'description' => 'Most damage dealt during Curses on Cursed Hollow.'],
            ['award_id' => 1004, 'title' => 'Dragonshire Shrines Captured', 'icon' => 'storm_ui_mvp_shriner', 'description' => 'Most shrine captures on Dragon Shire.'],
            ['award_id' => 1005, 'title' => 'Damage to Plants', 'icon' => 'storm_ui_mvp_guardianslayer', 'description' => 'Most damage dealt to Shamblers and neutral Garden Terrors on Garden of Terror.'],
            ['award_id' => 1005, 'title' => 'Garden Terror', 'icon' => 'storm_ui_mvp_gardenterror', 'description' => 'Most damage dealt to Shamblers and neutral Garden Terrors on Garden of Terror.'],
            ['award_id' => 1006, 'title' => 'Skull Collector', 'icon' => 'storm_ui_mvp_skullcollector', 'description' => 'Most skulls collected on Haunted Mines.'],
            ['award_id' => 1007, 'title' => 'Guardian Slayer', 'icon' => 'storm_ui_mvp_guardianslayer', 'description' => 'Most damage dealt to Shrine Guardians on Infernal Shrines.'],
            ['award_id' => 1008, 'title' => 'Temple Master', 'icon' => 'storm_ui_mvp_templemaster', 'description' => 'Most time to occupy a Temple on Sky Temple.'],
            ['award_id' => 1009, 'title' => 'Jeweler', 'icon' => 'storm_ui_mvp_jeweler', 'description' => 'Most gems turned in on Tomb of the Spider Queen.'],
            ['award_id' => 1010, 'title' => 'Most Altar Shots', 'icon' => 'storm_ui_mvp_cannoneer', 'description' => 'Most Core damage done on Towers of Doom.'],
            ['award_id' => 1012, 'title' => 'Zerg Crusher', 'icon' => 'storm_ui_mvp_zergcrusher', 'description' => 'Most damage dealt against enemy Zerg on Braxis Holdout.'],
            ['award_id' => 1013, 'title' => 'Da Bomb', 'icon' => 'storm_ui_mvp_dabomb', 'description' => 'Most damage dealt with Nukes on Warhead Junction.'],
            ['award_id' => 1016, 'title' => 'Pusher', 'icon' => 'storm_ui_mvp_pusher', 'description' => 'Most time pushing a Payload on Hanamura.'],
            ['award_id' => 1019, 'title' => 'Pointguard', 'icon' => 'storm_ui_mvp_pointguard', 'description' => 'Most time to occupy a Control Point on Volskaya Foundry.'],
            ['award_id' => 1022, 'title' => 'Loyal Defender', 'icon' => 'storm_ui_mvp_loyaldefender', 'description' => 'Most interrupted cage unlock attempts on Alterac Pass.'],
            ['award_id' => 1023, 'title' => 'Seed Collector', 'icon' => 'storm_ui_mvp_gardenterror', 'description' => 'Most seeds collected on Garden of Terror.'],
        ];

        foreach ($data as $row) {
            DB::table('awards')->insert($row);
        }
    }
}
