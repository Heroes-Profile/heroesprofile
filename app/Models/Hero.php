<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
  protected $table = 'heroes';
  public $timestamps = false;
  protected $primaryKey = 'id';

}
