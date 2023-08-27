<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class BattlenetAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'battlenet_accounts';
    protected $primaryKey = 'battlenet_accounts_id';
    protected $connection = 'heroesprofile';

    protected $fillable = [
        'battlenet_accounts_id',
        'battlenet_id',
        'battletag',
        'battlenet_access_token',
        'remember_token',
        'updated_at',
        'created_at'
    ];
}
