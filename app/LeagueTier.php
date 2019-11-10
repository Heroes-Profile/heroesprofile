<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeagueTier extends Model
{
  protected $fillable = ['tier_id', 'name',];

  protected $primaryKey = 'tier_id';

}
