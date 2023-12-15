<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeaderAlertSeeder extends Seeder
{
    public function run()
    {
        foreach ($data as $row) {
            DB::table('header_alert')->insert([
                'cateogry' => $row['cateogry'],
                'text' => $row['text'],
                'valid' => $row['valid'],
            ]);
        }
    }
}
