<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class PartyCombinationRule implements Rule
{
    public function passes($attribute, $value)
    {
        $validCombinations = ['00005', '00023', '00041', '00320', '04001', '50000'];
        return in_array($value, $validCombinations) ? $value : false;
    }

    public function message()
    {
        return 'The selected mmr types are invalid.';
    }
}
