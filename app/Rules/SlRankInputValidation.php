<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SlRankInputValidation implements Rule
{
    /** sl_leagues codes to real_league_tier. */
    public const TIERS = [
        'bronze' => 1,
        'silver' => 2,
        'gold' => 3,
        'platinum' => 4,
        'diamond' => 5,
        'master' => 6,
        'grandmaster' => 7,
    ];

    public function passes($attribute, $value)
    {
        if (! is_array($value)) {
            $value = explode(',', $value);
        }

        return $value !== [] && array_diff($value, array_keys(self::TIERS)) === [];
    }

    public function message()
    {
        return 'The :attribute must be a valid SL rank.';
    }
}
