<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

use App\Models\SeasonDate;

class SeasonInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validSeasons = SeasonDate::pluck('id')->toArray();
        return in_array($value, $validSeasons) ? $value : false;
    }

    public function message()
    {
        return 'The selected seasons are invalid.';
    }
}
