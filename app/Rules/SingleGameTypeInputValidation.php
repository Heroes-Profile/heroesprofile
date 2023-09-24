<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\GameType;

class SingleGameTypeInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        $typeId = GameType::where('short_name', $value)
                    ->pluck('type_id')->first();

        if(!$typeId){
            return 5;
        }else{
            return $typeId;
        }
    }

    public function message()
    {
        return 'The selected game types are invalid.';
    }
}
