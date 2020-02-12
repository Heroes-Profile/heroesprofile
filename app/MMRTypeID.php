<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MMRTypeID extends Model
{
  protected $table = 'mmr_type_ids';
  protected $fillable = ['mmr_type_id', 'name'];
  protected $primaryKey = 'mmr_type_ids';
  public $timestamps = false;
}
