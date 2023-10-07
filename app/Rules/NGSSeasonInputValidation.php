<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

use App\Models\NGS\NGSTeam;

class NGSSeasonInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validSeasons = NGSTeam::distinct('season')->pluck('season')->toArray();
        return in_array($value, $validSeasons);
    }

    public function message()
    {
        return 'The selected seasons are invalid.';
    }
}
