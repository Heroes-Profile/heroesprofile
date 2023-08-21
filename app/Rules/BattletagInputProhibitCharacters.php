<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BattletagInputProhibitCharacters implements Rule
{
    protected $prohibitedCharacters = [' ', '?', '%'];

    public function passes($attribute, $value)
    {
        foreach ($this->prohibitedCharacters as $character) {
            if (strpos($value, $character) !== false) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must not contain prohibited characters.';
    }
}
