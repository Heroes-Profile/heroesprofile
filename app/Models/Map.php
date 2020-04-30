<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
  protected $table = 'maps';
  public $timestamps = false;
  protected $primaryKey = 'map_id';
}
