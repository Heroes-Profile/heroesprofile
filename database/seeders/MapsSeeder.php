<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['1', 'Battlefield of Eternity', 'BattlefieldOfEternity', 'standard', '1', '1'],
            ['2', 'Blackheart\'s Bay', 'BlackheartsBay', 'standard', '0', '1'],
            ['3', 'Braxis Holdout', 'BraxisHoldout', 'standard', '1', '1'],
            ['4', 'Cursed Hollow', 'CursedHollow', 'standard', '1', '1'],
            ['5', 'Dragon Shire', 'DragonShire', 'standard', '1', '1'],
            ['6', 'Garden of Terror', 'HauntedWoods', 'standard', '1', '1'],
            ['7', 'Hanamura Temple', 'Hanamura', 'standard', '1', '1'],
            ['8', 'Haunted Mines', 'HauntedMines', 'standard', '0', '0'],
            ['9', 'Infernal Shrines', 'Shrines', 'standard', '1', '1'],
            ['10', 'Sky Temple', 'ControlPoints', 'standard', '1', '1'],
            ['11', 'Tomb of the Spider Queen', 'Crypts', 'standard', '1', '1'],
            ['12', 'Towers of Doom', 'TowersOfDoom', 'standard', '1', '1'],
            ['13', 'Warhead Junction', 'Warhead Junction', 'standard', '1', '1'],
            ['14', 'Volskaya Foundry', 'Volskaya', 'standard', '1', '1'],
            ['15', 'Alterac Pass', 'AlteracPass', 'standard', '1', '1'],
            ['16', 'Escape From Braxis', 'EscapeFromBraxis', 'brawl', '0', '0'],
            ['17', 'Industrial District', 'IndustrialDistrict', 'ARAM', '0', '1'],
            ['18', 'Lost Cavern', 'LostCavern', 'ARAM', '0', '1'],
            ['19', 'Pull Party', 'PullParty', 'brawl', '0', '0'],
            ['20', 'Silver City', 'SilverCity', 'ARAM', '0', '1'],
            ['21', 'Braxis Outpost', 'BraxisOutpost', 'ARAM', '0', '1'],
            ['22', 'Checkpoint: Hanamura', 'HanamuraPayloadPush', 'brawl', '0', '0'],
            ['23', 'Escape From Braxis (Heroic)', 'EscapeFromBraxis(Heroic)', 'brawl', '0', '0'],

        ];

        foreach ($data as $row) {
            DB::table('maps')->insert([
                'map_id' => $row[0],
                'name' => $row[1],
                'short_name' => $row[2],
                'type' => $row[3],
                'ranked_rotation' => $row[4],
                'playable' => $row[5],
            ]);
        }
    }
}
