<?php

namespace App\Rules;

use App\Models\MMRTypeID;
use Illuminate\Contracts\Validation\Rule;

class MMRTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validTypes = MMRTypeID::pluck('name')->toArray();

        return in_array($value, $validTypes);
    }

    public function message()
    {
        return 'The selected mmr types are invalid.';
    }
}
