<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePageCache extends Model
{
  protected $table = 'profile_page';
  protected $primaryKey = 'profile_page_id';
  public $timestamps = false;
  //protected $connection= 'mysql_cache';
  protected $connection= 'mysql_dev';

  public function scopeFilters($query, $blizz_id, $region, $game_type, $season){
    $query->where('blizz_id', $blizz_id);
    $query->where('region', $region);
    if($game_type != ""){
      $query->where('game_type', $game_type);
    }
    if($season != ""){
      $query->where('season', $season);
    }
    return $query;
  }
}
