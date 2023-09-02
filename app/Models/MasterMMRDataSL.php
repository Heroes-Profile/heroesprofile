<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterMMRDataSL extends Model
{
  protected $table = 'master_mmr_data_sl';
  protected $primaryKey = 'master_mmr_data_sl_id';
  protected $connection = 'heroesprofile';

  public $timestamps = false;

  protected $appends = ['win_rate'];
  
    public function scopeFilterByType($query, $type)
    {
      return $query->where('type_value', $type);
    }


    public function scopeFilterByGametype($query, $game_type)
    {
      return $query->where('game_type', $game_type);
    }


    public function scopeFilterByBlizzID($query, $blizz_id)
    {
      return $query->where('blizz_id', $blizz_id);
    }

    public function scopeFilterByRegion($query, $region)
    {
      return $query->where('region', $region);
    }

    public function getWinRateAttribute()
    {
        $totalGames = $this->win + $this->loss;
        
        if ($totalGames === 0) {
            return 0;
        }

        return ($this->win / $totalGames) * 100;
    }
}