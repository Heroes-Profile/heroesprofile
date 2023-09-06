<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\GameType;

class SingleGameTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validGameTypes = GameType::pluck('short_name')->toArray();
        $validGameTypes = array_diff($validGameTypes, ['br', 'cu']);

        if(!in_array($value, $validGameTypes)){  // corrected from $this->validGameTypes
            return 5;
        }

        // Assume $filteredGameTypes is defined elsewhere or pass it as an argument
        $typeId = GameType::whereIn('short_name', $filteredGameTypes)
                    ->pluck('type_id')->first(); // corrected from ->type_id

        return $typeId;
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
