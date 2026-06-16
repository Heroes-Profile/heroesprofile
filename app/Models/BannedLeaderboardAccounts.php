<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedLeaderboardAccounts extends Model
{
    protected $table = 'banned_leaderboard_accounts';

    protected $primaryKey = 'banned_leaderboard_accounts';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
