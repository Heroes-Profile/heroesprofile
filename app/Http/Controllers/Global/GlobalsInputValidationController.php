<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\RegionInputValidation;

class GlobalsInputValidationController extends Controller
{
    public function globalsValidationRules()
    {
        return [
            'timeframe_type' => 'required|in:minor,major',           
            'timeframe' => ['required', new TimeframeMinorInputValidation()],
            'game_type' => ['required', new GameTypeInputValidation()],
            'league_tier' => ['sometimes', 'nullable', new TierByIDInputValidation()],
            'hero_league_tier' => ['sometimes', 'nullable', new TierByIDInputValidation()],
            'role_league_tier' => ['sometimes', 'nullable', new TierByIDInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation()],
            'mirror' => 'sometimes|in:null,0,1',   
            'region' => ['sometimes', 'nullable', new RegionInputValidation()],
        ];
    }
}
