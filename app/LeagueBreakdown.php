<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeagueBreakdown extends Model
{
  protected $fillable = ['type_role_hero', 'game_type', 'league_tier', 'min_mmr'];
  protected $table = 'league_breakdowns';

  protected $primaryKey = ['type_role_hero', 'game_type', 'league_tier'];
}
