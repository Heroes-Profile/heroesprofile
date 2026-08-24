<?php

namespace App\Rules;

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

    /** The permitted values, so the docs can list what this accepts. */
    public function allowed(): array
    {
        return $this->validStackSize;
    }

    public function passes($attribute, $value)
    {
        // Global hero stats sends several at once; everywhere else sends one.
        foreach ((array) $value as $entry) {
            if (! array_key_exists($entry, $this->validStackSize)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must be a valid stack size.';
    }
}
