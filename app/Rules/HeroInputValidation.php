<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

use App\Models\Hero;

class HeroInputValidation implements Rule
{

    public function passes($attribute, $value)
    {
        $validHeroNames = Hero::pluck("name")->toArray();
        
        if (in_array($value, $validHeroNames)) {
            $hero = Hero::where('name', $value)->first();
            if ($hero) {
                return $hero->id;
            }
        }

        return false;
    }

    public function message()
    {
        return 'The selected hero name is invalid.';
    }
}
