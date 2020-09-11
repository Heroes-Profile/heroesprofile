<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplayBan extends Model
{
  protected $table = 'replay_bans';
  public $timestamps = false;
  protected $primaryKey = 'ban_id';
}
