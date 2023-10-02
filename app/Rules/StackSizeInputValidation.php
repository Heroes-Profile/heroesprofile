<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class StackSizeInputValidation implements Rule
{
    protected $validStackSize = [
        'All' => 0,
        'Solo' => 1,
        'Duo' => 2,
        '3 Players' => 3,
        '4 Players' => 4,
        '5 Players' => 5,
    ];

    public function passes($attribute, $value)
    {
        return array_key_exists($value, $this->validStackSize);
    }

    public function message()
    {
        return 'The :attribute must be a valid stack size.';
    }
}
