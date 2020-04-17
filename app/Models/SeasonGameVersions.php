<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonGameVersions extends Model
{
  protected $fillable = ['season', 'game_version'];
  protected $table = 'season_game_versions';
  public $timestamps = false;
}
