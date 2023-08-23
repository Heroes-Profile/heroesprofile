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
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $filteredRegions = array_intersect($value, $this->validRegions);

        return $filteredRegions ?: [];
    }

    public function message()
    {
        return 'The :attribute must be a valid region value.';
    }
}
