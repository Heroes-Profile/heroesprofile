<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use App\Models\Map;

class GameMapInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
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
