<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

use App\Models\NGS\NGSTeam;

class NGSDivisionInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validSeasons = NGSTeam::distinct('division')->pluck('division')->toArray();
        return in_array($value, $validSeasons);
    }

    public function message()
    {
        return 'The selected divisions are invalid.';
    }
}
