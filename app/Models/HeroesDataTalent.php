<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroesDataTalent extends Model
{
    protected $table = 'heroes_data_talents';

    protected $primaryKey = 'talent_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope('playable', function (Builder $builder) {
            $builder->where('status', 'playable');
        });
    }

    public function globalHeroTalentDetail()
    {
        return $this->hasOne(GlobalHeroTalentDetails::class, 'talent_id', 'talent');
    }

    public function GlobalHeroTalentsVersusHeroes()
    {
        return $this->hasOne(GlobalHeroTalentDetails::class, 'talent_id', 'talent');
    }

    public function GlobalHeroTalentsWithHeroes()
    {
        return $this->hasOne(GlobalHeroTalentDetails::class, 'talent_id', 'talent');
    }
}
