<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedAccountsNote extends Model
{
    protected $table = 'banned_accounts_notes';

    protected $primaryKey = 'banned_accounts_notes_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
