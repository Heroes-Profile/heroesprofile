<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeagueTiersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
          ['0', 'wood'],
          ['1', 'bronze'],
          ['2', 'silver'],
          ['3', 'gold'],
          ['4', 'platinum'],
          ['5', 'diamond'],
          ['6', 'master'],
          ['7', 'all'],
        ];

        foreach ($data as $row) {
          DB::table('league_tiers')->insert([
              'tier_id' => $row[0],
              'name' => $row[1],
          ]);
      }
    }
}
