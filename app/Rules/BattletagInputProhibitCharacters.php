<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class BattletagInputProhibitCharacters implements ValidationRule
{
    protected $prohibitedCharacters = [' ', '?', '%'];

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (str_contains($value, $this->prohibitedCharacters)) {
            $fail('The :attribute must be uppercase.');
        }
    }
}
