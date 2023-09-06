<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

use Closure;
use App\Models\MMRTypeID;

class MMRTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validTypes = MMRTypeID::pluck('name')->toArray();
        
        if(in_array($value, $validTypes)){
            return $typeId = MMRTypeID::where('name', $value)
                ->pluck('mmr_type_id')->first();
        }

        return false;
    }

    public function message()
    {
        return 'The selected mmr types are invalid.';
    }
}
