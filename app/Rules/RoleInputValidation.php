<?php

namespace App\Rules;

use App\Models\Hero;
use Illuminate\Contracts\Validation\Rule;

class RoleInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validRoles = Hero::pluck('new_role')->toArray();

        if (! in_array($value, $validRoles)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected role is invalid.';
    }
}
