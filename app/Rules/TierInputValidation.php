<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\LeagueTier;


class TierInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validTier = LeagueTier::pluck('name')->toArray();
                
        $filteredTiers = array_intersect($value, $validTier);
        
        if (empty($filteredTiers)) {
            // Return "" as the default game type
            return [];
        }

        $typeIds = LeagueTier::whereIn('name', $filteredTiers)
            ->pluck('tier_id')
            ->toArray();
        return $typeIds;
    }

    public function message()
    {
        return 'The selected tiers are invalid.';
    }
}
