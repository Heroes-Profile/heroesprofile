<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
  protected $table = 'leaderboard';
  protected $primaryKey = 'leaderboard_id';
  public $timestamps = false;
  protected $connection= 'mysql_cache';

  protected $fillable = ['rank', 'split_battletag', 'battletag', 'blizz_id', 'region', 'win_rate', 'win', 'loss', 'games_played', 'conservative_rating', 'rating'];

  public function scopeFilters($query, $game_type, $season, $region, $type, $page){
    $query->where('game_type', $game_type);
    $query->where('season', $season);
    $query->where('type', $type);

    if($region != ""){
      $query->where('region', $region);
    }

    //Need to add some paging for this page later.  Currently it is limited to 250, but can expand it
    $page = 1;

    return $query;
  }
}
