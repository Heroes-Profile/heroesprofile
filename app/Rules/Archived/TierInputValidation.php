<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\LeagueTier;


class TierInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if(!$value){
            return [];
        }
        
        $validTier = LeagueTier::pluck('name')->toArray();
                
        $filteredTiers = array_intersect($value, $validTier);
        
        if (empty($filteredTiers)) {
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
