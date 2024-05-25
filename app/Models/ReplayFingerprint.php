<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplayFingerprint extends Model
{
  protected $table = 'replay_fingerprints';

  protected $primaryKey = 'replayID';

  protected $connection = 'heroesprofile';

  public $timestamps = false;
}
