<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class HeroLevelInputValidation implements Rule
{
    protected $validLevels = [
        1, 5, 10, 15, 25, 40, 60, 80, 100
    ];

    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $filteredLevels = array_intersect($value, $this->validLevels);
        $filteredLevels = array_map('intval', $filteredLevels); // Cast to integers

      if(count($filteredLevels) == 9){
            return [];
        }

        return $filteredLevels ?: [];
    }

    public function message()
    {
        return 'The :attribute must be a valid hero level.';
    }
}
