<?php

namespace App\Rules;

use App\Models\LeagueTier;
use Illuminate\Contracts\Validation\Rule;

class TierInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Ensure $value is an array
        if (! is_array($value)) {
            return false;
        }

        // Fetch valid tiers from the database and convert both tier_id and name to lowercase
        $validTiers = LeagueTier::select('tier_id', 'name')
            ->get()
            ->map(function ($tier) {
                return [
                    'tier_id' => $tier->tier_id,
                    'name' => strtolower($tier->name),
                ];
            })
            ->toArray();

        // Convert incoming values to lowercase
        $value = array_map('strtolower', $value);

        // Check for intersection after converting to lowercase
        $filteredTiers = array_intersect($value, array_column($validTiers, 'tier_id'), array_column($validTiers, 'name'));

        return !empty($filteredTiers);
    }

    public function message()
    {
        return 'The selected league tiers are invalid.';
    }
}
