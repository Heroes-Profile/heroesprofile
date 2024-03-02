<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearExpiredSessions extends Command
{
    protected $signature = 'sessions:clear';
    protected $description = 'Clear expired sessions';

    public function handle()
    {
        $expiredAt = now()->subMinutes(config('session.lifetime'));

        DB::table('heroesprofile_cache.sessions')
            ->where('last_activity', '<', $expiredAt)
            ->delete();

        $this->info('Expired sessions cleared successfully.');
    }
}
