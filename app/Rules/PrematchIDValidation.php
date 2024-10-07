<?php

namespace App\Rules;

use App\Models\Prematch;
use Illuminate\Contracts\Validation\Rule;

class PrematchIDValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the value is an integer
        if (! is_numeric($value)) {
            return false;
        }

        // Check if the replayID exists in the Replay table
        return Prematch::where('prematch_replayID', $value)->exists();
    }

    public function message()
    {
        return 'The prematch ID is invalid.';
    }
}
