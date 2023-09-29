<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Map;

class SingleGameMapInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $validMaps = Map::where('playable', '<>', 0)
            ->pluck('name')
            ->toArray();
        
        if(!in_array($value, $validMaps)){
            return;
        }

        $mapId = Map::where('name', $value)
                    ->pluck('map_id')->first();

        return $mapId;
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
