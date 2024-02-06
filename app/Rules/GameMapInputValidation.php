<?php

namespace App\Rules;

use App\Models\Map;
use Illuminate\Contracts\Validation\Rule;

class GameMapInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if (! is_array($value)) {
          $value = explode(',', $value);
        }

        $validMaps = Map::where('playable', '<>', 0)
            ->pluck('name')
            ->toArray();
        $filteredMaps = array_intersect($value, $validMaps);

        if (empty($filteredMaps)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected game maps are invalid.';
    }
}
