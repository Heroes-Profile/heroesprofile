<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
  protected $table = 'leaderboard';
  protected $primaryKey = 'leaderboard_id';
  public $timestamps = false;
  protected $connection= 'mysql_cache';
}
