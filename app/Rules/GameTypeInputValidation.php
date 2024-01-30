<?php

namespace App\Rules;

use App\Models\GameType;
use Illuminate\Contracts\Validation\Rule;

class GameTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Convert single game type to an array
        if (! is_array($value)) {
          $value = explode(',', $value);
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
