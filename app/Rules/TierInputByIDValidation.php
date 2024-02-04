<?php

namespace App\Rules;

use App\Models\LeagueTier;
use Illuminate\Contracts\Validation\Rule;

class TierInputByIDValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (! is_array($value)) {
          $value = explode(',', $value);
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