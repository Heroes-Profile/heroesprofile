<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class DateInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Attempt to parse the date using Carbon
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Exception $e) {
            return false; // Parsing failed, date is invalid
        }

        // Check if the parsed date is valid
        return $parsedDate instanceof Carbon && $parsedDate->format('Y-m-d') === $value;
    }

    public function message()
    {
        return 'The selected game date is invalid. Please use the format YYYY-MM-DD.';
    }
}
