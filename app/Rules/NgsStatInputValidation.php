<?php

namespace App\Rules;

use App\Services\Api\NgsLeaderboardService;
use Illuminate\Contracts\Validation\Rule;

class NgsStatInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        return in_array($value, NgsLeaderboardService::STATS, true);
    }

    public function message()
    {
        return 'The selected stat is invalid.';
    }
}
