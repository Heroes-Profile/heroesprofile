<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use App\Models\GameType;

class GameTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        $existingGameTypes = GameType::pluck('short_name')->toArray();
        $validGameTypes = array_intersect($value, $existingGameTypes);

        if (empty($validGameTypes)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
