<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattlenetAccountFlair extends Model
{
    public const XALATATH_EYE = 'xalatath_eye';

    protected $table = 'battlenet_account_flair';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    protected $fillable = [
        'battlenet_accounts_id',
        'flair',
        'awarded_at',
        'ad_free_until',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
        'ad_free_until' => 'datetime',
    ];

    public function battlenetAccount()
    {
        return $this->belongsTo(BattlenetAccount::class, 'battlenet_accounts_id', 'battlenet_accounts_id');
    }
}
