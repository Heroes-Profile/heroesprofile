<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** One active site-wide alert, so the header shows one locally. */
class HeaderAlertSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('header_alert')->insert([
            'header_alert_id' => 1,
            'cateogry' => 'info',
            'text' => 'Replay processing is running behind; new uploads may take a few hours to appear.',
            'valid' => 1,
        ]);
    }
}
