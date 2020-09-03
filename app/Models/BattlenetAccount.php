<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class BattlenetAccount  extends Authenticatable
{
  protected $table = 'battlenet_accounts';
  protected $primaryKey = 'battlenet_accounts_id';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'battlenet_id', 'battletag', 'region', 'battlenet_access_token', 'remember_token'
  ];
}
