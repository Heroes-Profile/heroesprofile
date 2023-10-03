<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

use App\Models\NGS\Team;

class NGSDivisionInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validSeasons = Team::distinct('division')->pluck('division')->toArray();
        return in_array($value, $validSeasons);
    }

    public function message()
    {
        return 'The selected divisions are invalid.';
    }
}
