<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattlenetUserSetting extends Model
{
    protected $table = 'battlenet_user_settings';
    protected $primaryKey = 'battlenet_user_settings_id';
    protected $connection = 'heroesprofile';

    public function battlenetAccount()
    {
        return $this->belongsTo(BattlenetAccount::class, 'battlenet_accounts_id', 'battlenet_accounts_id');
    }
}
