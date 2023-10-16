<?php

namespace App\Rules;

use App\Models\NGS\NGSTeam;
use Illuminate\Contracts\Validation\Rule;

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
