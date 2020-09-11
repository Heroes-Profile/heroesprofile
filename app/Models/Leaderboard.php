<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
  protected $table = 'leaderboard';
  protected $primaryKey = 'leaderboard_id';
  public $timestamps = false;
  protected $connection= 'mysql_cache';
  protected $fillable = ['rank', 'split_battletag', 'battletag', 'blizz_id', 'region', 'win_rate', 'win', 'loss', 'games_played', 'conservative_rating', 'rating', 'most_played_hero', 'hero_build_games_played'];

  public function scopeFilters($query, $game_type, $season, $region, $type, $cache_number){
    $query->where('game_type', $game_type);
    $query->where('season', $season);
    $query->where('heroesprofile_cache.leaderboard.type', $type);

    if($region != ""){
      $query->where('region', $region);
    }

    $query->where('cache_number', $cache_number);
    //Need to add some paging for this page later.
    $page = 1;

    return $query;
  }


  /**
  * Get the player's Rating value
  *
  * @param  string  $value
  * @return string
  */
  public function getRatingAttribute($value)
  {
    return number_format((float) $value, 2);
  }

  /**
  * Get the player's Win Rate value
  *
  * @param  string  $value
  * @return string
  */
  public function getWinRateAttribute($value)
  {
    return number_format((float) $value, 2);
  }

  /**
 * Get the player's MMR value
 *
 * @param  string  $value
 * @return float
 */
  public function getMmrAttribute($value)
  {
      return round(1800 + 40 * (float) $value);
  }
}
