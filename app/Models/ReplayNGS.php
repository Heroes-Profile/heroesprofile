<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplayNGS extends Model
{
  protected $table = 'replay';
  public $timestamps = false;
  protected $primaryKey = 'replayID';
  protected $connection= 'mysql_ngs';
}
