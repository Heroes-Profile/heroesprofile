<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroStats extends Model
{
  protected $table = 'global_hero_stats';
  protected $primaryKey = 'global_hero_id';
  protected $connection = 'heroesprofile';

  public $timestamps = false;

  public function scopeFilterByGameVersion($query, $gameVersion)
  {
      return $query->whereIn('game_version', $gameVersion);
  }

  public function scopeFilterByGameType($query, $gameType)
  {
      return $query->where('game_type', $gameType);
  }

  public function scopeFilterByLeagueTier($query, $leagueTier)
  {
    if (!empty($leagueTier)) {
      return $query->where('league_tier', $leagueTier);
    }
    return $query;
  }

  public function scopeFilterByHeroLeagueTier($query, $heroLeagueTier)
  {
    if (!empty($heroLeagueTier)) {
      return $query->where('hero_league_tier', $heroLeagueTier);
    }
    return $query;
  }

  public function scopeFilterByRoleLeagueTier($query, $roleLeagueTier)
  {
    if (!empty($roleLeagueTier)) {
      return $query->where('role_league_tier', $roleLeagueTier);
    }
    return $query;
  }

  public function scopeFilterByHeroLevel($query, $heroLevel)
  {
    if (!empty($heroLevel)) {
      return $query->where('hero_level', $heroLevel);
    }
    return $query;
  }

  public function scopeFilterByGameMap($query, $gameMap)
  {
    if (!empty($gameMap)) {
      return $query->where('game_map', $gameMap);
    }
    return $query;
  }

  public function scopeExcludeMirror($query, $mirror)
  {
      return $query->where('mirror', $mirror);
  }
}