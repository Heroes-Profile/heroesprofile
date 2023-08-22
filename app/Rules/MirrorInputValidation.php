<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MirrorInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if ($value === '' || $value === 'Exclude') {
            return 0;
        } elseif ($value === 'Include') {
            return 1;
        }
        return 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be either "Include", "Exclude", or empty.';
    }
}
