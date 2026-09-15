<?php

namespace App\Rules;

use App\Models\SeasonDate;
use Illuminate\Contracts\Validation\Rule;

class SeasonInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validSeasons = SeasonDate::pluck('id')->toArray();

        if ($value == 'All') {
            return true;
        }

        if (is_array($value)) {
            return collect($value)->every(fn ($season) => in_array($season, $validSeasons));
        }

        return in_array($value, $validSeasons);
    }

    public function message()
    {
        return 'The selected seasons are invalid.';
    }
}
