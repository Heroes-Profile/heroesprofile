<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use App\Models\MMRTypeID;

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
