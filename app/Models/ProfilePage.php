<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePage extends Model
{
    protected $table = 'profile_page';

    protected $primaryKey = 'profile_page_id';

    protected $connection = 'heroesprofile_cache';

    public $timestamps = false;

    public function scopeFilterByBlizzID($query, $blizz_id)
    {
        return $query->where('blizz_id', $blizz_id);
    }

    public function scopeFilterByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /*
          public function scopeFilterByRoleLeagueTier($query, $roleLeagueTier)
      {
        if (!empty($roleLeagueTier)) {
          return $query->whereIn('role_league_tier', $roleLeagueTier);
        }
        return $query;
      }
      */
}
