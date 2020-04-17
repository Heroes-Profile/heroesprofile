<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroChange extends Model
{
  protected $table = 'global_hero_change';
  protected $primaryKey = 'global_hero_change_id';
  public $timestamps = false;
  protected $connection= 'mysql_cache';

  public function scopeFilters($query, $timeframe, $game_type){
    $query->where('game_version', $timeframe);
    $query->where('game_type', $game_type);

    return $query;
  }
}
