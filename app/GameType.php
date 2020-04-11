<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
  protected $fillable = ['type_id', 'name', 'short_name'];
  protected $table = 'game_types';
  public $timestamps = false;

}
