<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TalentBuildTypeInputValidation implements Rule
{
    protected $validBuildTypes = [
        "Popular", "HP Algorithm", "Unique Lvl 1", "Unique Lvl 4", "Unique Lvl 7", "Unique Lvl 10", "Unique Lvl 13", "Unique Lvl 16", "Unique Lvl 20"
    ];

    public function passes($attribute, $value)
    {

        if(in_array($value, $this->validBuildTypes)){
            return $value;
        }

        return false;
    }

    public function message()
    {
        return 'The :attribute must be a valid hero level.';
    }
}
