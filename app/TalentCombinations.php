<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TalentCombinations extends Model
{
  protected $table = 'talent_combinations';
  protected $primaryKey = 'talent_combination_id';
  public $timestamps = false;
}
