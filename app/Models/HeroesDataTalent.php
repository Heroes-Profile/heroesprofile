<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroesDataTalent extends Model
{
  protected $table = 'heroes_data_talents';
  public $timestamps = false;
  protected $primaryKey = 'talent_id';

}
