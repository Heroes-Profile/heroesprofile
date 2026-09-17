<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatreonAccount extends Model
{
    protected $table = 'patreon_accounts';

    protected $primaryKey = 'patreon_accounts_id';

    protected $connection = 'heroesprofile';

    /** Serialized into page HTML along with the user; see BattlenetAccount::$hidden. */
    protected $hidden = [
        'access_token',
        'remember_token',
        'expires_in',
        'email',
    ];

    protected $fillable = [
        'battlenet_accounts_id',
        'patreon_id',
        'name',
        'email',
        'site_flair',
        'ad_free',
        'access_token',
        'remember_token',
        'expires_in',
        'updated_at',
        'created_at',
    ];

    public function battlenetAccount()
    {
        return $this->belongsTo(BattlenetAccount::class, 'battlenet_accounts_id', 'battlenet_accounts_id');
    }
}
