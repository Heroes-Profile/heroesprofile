<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

use App\Models\LeagueTier;

class TierByIDInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (!is_array($value)) {
            return false;
        }

        $validTier = LeagueTier::pluck('tier_id')->toArray();
        $filteredTiers = array_intersect($value, $validTier);
        

        if (empty($filteredTiers)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'The selected league tiers are invalid.';
    }
}
