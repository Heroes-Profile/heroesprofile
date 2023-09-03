<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\LeagueTier;


class TierByIDInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        if(!$value){
            return [];
        }
        
        $validTier = LeagueTier::pluck('tier_id')->toArray();
                
        $filteredTiers = array_intersect($value, $validTier);
        
        if (empty($filteredTiers) || count($filteredTiers) == 7) {
            return [];
        }

        return $filteredTiers;
    }

    public function message()
    {
        return 'The selected tiers are invalid.';
    }
}
