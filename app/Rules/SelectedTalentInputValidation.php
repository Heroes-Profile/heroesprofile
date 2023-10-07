<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SelectedTalentInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if selectedtalents is empty or null
        if (empty($value) || is_null($value)) {
            return true;
        }

        // Check if all values are either null or numbers
        foreach ($value as $tier => $talent) {
            if (!is_null($talent) && !is_numeric($talent)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The selected talents are invalid.';
    }
}
