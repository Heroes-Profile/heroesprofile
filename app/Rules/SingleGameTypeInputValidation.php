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

        if(!in_array($value, $validGameTypes)){
            return 5;
        }

        $typeId = GameType::whereIn('short_name', $filteredGameTypes)
                    ->pluck('type_id')->first();

        return $typeId;
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
