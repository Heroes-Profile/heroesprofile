<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use App\Models\SeasonGameVersion;

class TimeframeMinorInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (!is_array($value)) {
            return false;
        }

        $existingVersions = SeasonGameVersion::pluck('game_version')->toArray();
        $validVersions = array_intersect($value, $existingVersions);

        if (empty($validVersions)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected game versions are invalid.';
    }
}
