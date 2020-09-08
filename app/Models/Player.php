<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
  protected $table = 'player';
  public $timestamps = false;
  protected $primaryKey = 'player_table_id';
}
