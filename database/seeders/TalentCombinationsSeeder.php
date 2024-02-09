<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TalentCombinationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [1, 1, 2, 3, 4, 5, 6, 7],
            // Add more rows as needed
        ];

        foreach ($data as $row) {
            DB::table('talent_combinations')->insert([
                'hero' => $row[0],
                'level_one' => $row[1],
                'level_four' => $row[2],
                'level_seven' => $row[3],
                'level_ten' => $row[4],
                'level_thirteen' => $row[5],
                'level_sixteen' => $row[6],
                'level_twenty' => $row[7],
            ]);
        }
    }
}