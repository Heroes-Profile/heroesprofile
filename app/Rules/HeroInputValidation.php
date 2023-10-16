<?php

namespace App\Rules;

use App\Models\Hero;
use Illuminate\Contracts\Validation\Rule;

class HeroInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validHeroNames = Hero::pluck('name')->toArray();

        if (! in_array($value, $validHeroNames)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected hero name is invalid.';
    }
}
