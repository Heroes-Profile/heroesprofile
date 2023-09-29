<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

use App\Models\Hero;

class HeroInputByIDValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validHeroIDs = Hero::pluck("id")->toArray();
        
        if (!in_array($value, $validHeroIDs)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected hero id is invalid.';
    }
}
