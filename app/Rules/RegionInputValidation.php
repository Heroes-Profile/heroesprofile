<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RegionInputValidation implements Rule
{
    protected $validRegions = [
        "NA", "EU", "KR", "CN"
    ];

    public function passes($attribute, $value)
    {

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid region value.';
    }
}
