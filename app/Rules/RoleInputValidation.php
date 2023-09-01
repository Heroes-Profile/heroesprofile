<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

use Closure;
use App\Models\Hero;

class RoleInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validRoles = Hero::pluck('new_role')->toArray();
        
        if(in_array($value, $validRoles)){
            return $value;
        }

        return false;
    }

    public function message()
    {
        return 'The selected mmr types are invalid.';
    }
}
