<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroComposition extends Model
{
    protected $table = 'hero_compositions';

    protected $primaryKey = 'hero_composition_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function globalHeroCompositions()
    {
        return $this->hasMany(GlobalHeroCompositions::class, 'hero_composition_id', 'hero_composition_id');
    }
}
