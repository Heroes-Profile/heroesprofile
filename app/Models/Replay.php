<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replay extends Model
{
  protected $table = 'replay';
  public $timestamps = false;
  protected $primaryKey = 'replayID';
}
