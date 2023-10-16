<?php

namespace App\Rules;

use App\Models\Hero;
use Illuminate\Contracts\Validation\Rule;

class HeroInputByIDValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validHeroIDs = Hero::pluck('id')->toArray();

        if (! in_array($value, $validHeroIDs)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected hero id is invalid.';
    }
}
