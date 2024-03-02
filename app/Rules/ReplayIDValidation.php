<?php

namespace App\Rules;

use App\Models\Replay;
use Illuminate\Contracts\Validation\Rule;

class ReplayIDValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the value is an integer
        if (!is_numeric($value)) {
            return false;
        }

        // Check if the replayID exists in the Replay table
        return Replay::where('replayID', $value)->exists();
    }

    public function message()
    {
        return 'The replayID is invalid.';
    }
}
