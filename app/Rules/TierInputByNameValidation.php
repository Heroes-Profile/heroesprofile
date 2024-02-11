<?php

namespace App\Rules;

use App\Models\LeagueTier;
use Illuminate\Contracts\Validation\Rule;

class TierInputByNameValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (! is_array($value)) {
            $value = explode(',', $value);
        }

        $value = array_map('strtolower', $value);
        $validTiers = LeagueTier::pluck('name')->map('strtolower')->toArray();
        $filteredTiers = array_intersect($value, $validTiers);

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
