<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedAccount extends Model
{
    protected $table = 'banned_accounts';

    protected $primaryKey = 'banned_accounts_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
