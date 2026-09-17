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

        // Every entry must be a region: one bad value crashes the region-to-id lookup downstream.
        return $value !== [] && array_diff($value, array_keys($this->validRegions)) === [];
    }

    public function message()
    {
        return 'The :attribute must be a valid region value.';
    }
}
