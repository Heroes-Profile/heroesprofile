<?php

use Illuminate\Database\Seeder;

class game_types_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('heroesprofile.game_types')->insert([
        ['type_id' => -1, 'name' => 'Brawl', 'short_name' => 'br'],
        ['type_id' => 0, 'name' => 'Custom', 'short_name' => 'cu'],
        ['type_id' => 1, 'name' => 'Quick Match', 'short_name' => 'qm'],
        ['type_id' => 2, 'name' => 'Unranked Draft', 'short_name' => 'ud'],
        ['type_id' => 3, 'name' => 'Hero League', 'short_name' => 'hl'],
        ['type_id' => 4, 'name' => 'Team League', 'short_name' => 'tl'],
        ['type_id' => 5, 'name' => 'Storm League', 'short_name' => 'sl']
      ]);
    }
}
