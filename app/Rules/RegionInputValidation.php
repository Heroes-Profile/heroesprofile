<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RegionInputValidation implements Rule
{
    protected $validRegions = [
        'NA' => 1,
        'EU' => 2,
        'KR' => 3,
        'CN' => 5,
    ];

    public function passes($attribute, $value)
    {
        if (! is_array($value)) {
          $value = explode(',', $value);
        }

        $filteredRegions = array_intersect($value, array_keys($this->validRegions));
        if (empty($filteredRegions)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must be a valid region value.';
    }
}
