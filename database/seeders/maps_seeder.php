<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class maps_seeder extends Seeder
{

    /**
     * The database schema.
     *
     * @var DB
     */
    protected $connection;

    /**
     * Create a new seed instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = DB::connection(config('database.default'));
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->connection->table('maps')->insert([
        ["map_id" => "1","name" => "Battlefield of Eternity","short_name" => "BattlefieldOfEternity","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "2","name" => "Blackheart's Bay","short_name" => "BlackheartsBay","type" => "standard","ranked_rotation" => "0","playable" => "1"],["map_id" => "3","name" => "Braxis Holdout","short_name" => "BraxisHoldout","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "4","name" => "Cursed Hollow","short_name" => "CursedHollow","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "5","name" => "Dragon Shire","short_name" => "DragonShire","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "6","name" => "Garden of Terror","short_name" => "HauntedWoods","type" => "standard","ranked_rotation" => "0","playable" => "1"],["map_id" => "7","name" => "Hanamura Temple","short_name" => "Hanamura","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "8","name" => "Haunted Mines","short_name" => "HauntedMines","type" => "standard","ranked_rotation" => "0","playable" => "0"],["map_id" => "9","name" => "Infernal Shrines","short_name" => "Shrines","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "10","name" => "Sky Temple","short_name" => "ControlPoints","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "11","name" => "Tomb of the Spider Queen","short_name" => "Crypts","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "12","name" => "Towers of Doom","short_name" => "TowersOfDoom","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "13","name" => "Warhead Junction","short_name" => "Warhead Junction","type" => "standard","ranked_rotation" => "0","playable" => "1"],["map_id" => "14","name" => "Volskaya Foundry","short_name" => "Volskaya","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "15","name" => "Alterac Pass","short_name" => "AlteracPass","type" => "standard","ranked_rotation" => "1","playable" => "1"],["map_id" => "16","name" => "Escape From Braxis","short_name" => "EscapeFromBraxis","type" => "brawl","ranked_rotation" => "0","playable" => "0"],["map_id" => "17","name" => "Industrial District","short_name" => "IndustrialDistrict","type" => "brawl","ranked_rotation" => "0","playable" => "1"],["map_id" => "18","name" => "Lost Cavern","short_name" => "LostCavern","type" => "brawl","ranked_rotation" => "0","playable" => "1"],["map_id" => "19","name" => "Pull Party","short_name" => "PullParty","type" => "brawl","ranked_rotation" => "0","playable" => "0"],["map_id" => "20","name" => "Silver City","short_name" => "SilverCity","type" => "brawl","ranked_rotation" => "0","playable" => "1"],["map_id" => "21","name" => "Braxis Outpost","short_name" => "BraxisOutpost","type" => "brawl","ranked_rotation" => "0","playable" => "1"],["map_id" => "22","name" => "Checkpoint: Hanamura","short_name" => "HanamuraPayloadPush","type" => "brawl","ranked_rotation" => "0","playable" => "0"],["map_id" => "23","name" => "Escape From Braxis (Heroic)","short_name" => "EscapeFromBraxis(Heroic)","type" => "brawl","ranked_rotation" => "0","playable" => "0"]
      ]);

    }
}
