<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\RegionInputValidation;
use App\Rules\RoleInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\TierInputByIDValidation;
use App\Rules\TierInputByNameValidation;
use App\Rules\TimeframeMinorInputValidation;

class GlobalsInputValidationController extends Controller
{
    public function globalValidationRulesURLParam($timeframeType, $timeframe)
    {
        return [
            'timeframe_type' => 'sometimes|in:minor,major,last_update',
            'timeframe' => ['sometimes', 'nullable', new TimeframeMinorInputValidation($timeframeType)],
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'statfilter' => ['sometimes', 'nullable', new StatFilterInputValidation($timeframeType, $timeframe)],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation],
            'hero_league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation],
            'role_league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation],
            'mirror' => 'sometimes|in:null,0,1',
            'minimum_games' => 'sometimes|nullable|integer',
        ];
    }

    public function globalsValidationRules($timeframeType, $timeframe)
    {
        return [
            'timeframe_type' => 'required|in:minor,major,last_update',
            'timeframe' => $timeframeType !== 'last_update' ? ['required', new TimeframeMinorInputValidation($timeframeType)] : 'nullable',
            'game_type' => ['required', new GameTypeInputValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'statfilter' => ['sometimes', 'nullable', new StatFilterInputValidation($timeframeType, $timeframe)],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'hero_league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'role_league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'mirror' => 'sometimes|in:null,0,1',
        ];
    }
}
