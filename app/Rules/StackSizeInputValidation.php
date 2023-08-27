<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

use Closure;

class StackSizeInputValidation implements Rule
{
    protected $validStackSize = [
        0, 1, 2, 3, 4, 5
    ];

    public function passes($attribute, $value)
    {
        return in_array($value, $this->validStackSize) ? $value : false;
    }

    public function message()
    {
        return 'The :attribute must be a valid stack size.';
    }
}
