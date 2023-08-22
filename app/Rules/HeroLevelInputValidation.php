<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class HeroLevelInputValidation implements Rule
{
    protected $validLevels = [
        1, 5, 10, 15, 25, 40, 60, 80, 100
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $filteredLevels = array_intersect($value, $this->validLevels);
        $filteredLevels = array_map('intval', $filteredLevels); // Cast to integers

        return $filteredLevels ?: [];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid hero level.';
    }
}
