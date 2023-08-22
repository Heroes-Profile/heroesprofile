<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Map;

class GameMapInputValidation implements Rule
{
    public function passes($attribute, $value)
    {        
        $validMaps = Map::where('playable', '<>', 0)
            ->pluck('name')
            ->toArray();
        

        $filteredMaps = array_intersect($value, $validMaps);
        

        if (empty($filteredMaps)) {
            // Return "" as the default map
            return [];
        }

        $mapID = Map::whereIn('name', $filteredMaps)
            ->pluck('map_id')
            ->toArray();

        return $mapID;
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
