<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\GameType;

class GameTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validGameTypes = GameType::pluck('short_name')->toArray();
        
        $validGameTypes = array_diff($validGameTypes, ['br', 'cu']);
        
        $filteredGameTypes = array_intersect($value, $validGameTypes);
        
        if (empty($filteredGameTypes)) {
            // Return 5 as the default game type
            return [5];
        }

        $typeIds = GameType::whereIn('short_name', $filteredGameTypes)
            ->pluck('type_id')
            ->toArray();

        return $typeIds;
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
