<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\Hero;
use App\Models\Map;
use App\Models\MMRTypeID;
use App\Models\SeasonGameVersion;
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
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'region' => ['sometimes', 'nullable', new RegionInputValidation()],
            'statfilter' => ['sometimes', 'nullable', new StatFilterInputValidation($timeframeType, $timeframe)],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation()],
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation()],
            'hero_league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation()],
            'role_league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation()],
            'mirror' => 'sometimes|in:null,0,1',
            'minimum_games' => 'sometimes|nullable|integer',
        ];
    }

    public function globalsValidationRules($timeframeType, $timeframe)
    {
        return [
            'timeframe_type' => 'required|in:minor,major,last_update',
            'timeframe' => $timeframeType !== 'last_update' ? ['required', new TimeframeMinorInputValidation($timeframeType)] : 'nullable',
            'game_type' => ['required', new GameTypeInputValidation()],
            'region' => ['sometimes', 'nullable', new RegionInputValidation()],
            'statfilter' => ['sometimes', 'nullable', new StatFilterInputValidation($timeframeType, $timeframe)],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation()],
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation()],
            'hero_league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation()],
            'role_league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation()],
            'mirror' => 'sometimes|in:null,0,1',
        ];
    }

    public function getTimeframeFilterValues($timeframeType, $timeframes)
    {
        if ($timeframeType == 'major') {
            $query = SeasonGameVersion::select('game_version');

            foreach ($timeframes as $timeframe) {
                $query->orWhere('game_version', 'like', $timeframe.'%');
            }
            $gameVersion = $query->get()
                ->pluck('game_version')
                ->toArray();

            return $gameVersion;
        }

        return $timeframes;
    }

    public function getTimeFrameFilterValuesLastUpdate($hero)
    {
        $game_version = Hero::select('last_change_patch_version')->where('id', $hero)->first()->last_change_patch_version;

        $gameVersion = SeasonGameVersion::select('game_version')->where('game_version', '>=', $game_version)->get()->pluck('game_version')->toArray();

        return $gameVersion;
    }

    public function getRegionFilterValues($regions)
    {
        if (is_null($regions)) {
            return null;
        }

        if (! is_array($regions)) {
            return $this->globalDataService->getRegionStringToID()[$regions];
        }

        return array_map(function ($region) {
            return $this->globalDataService->getRegionStringToID()[$region];
        }, $regions);
    }

    public function getGameMapFilterValues($game_maps)
    {
        if (is_null($game_maps)) {
            return null;
        }
        $mapIds = Map::whereIn('name', $game_maps)->pluck('map_id')->toArray();

        return $mapIds;
    }

    public function getHeroFilterValue($hero)
    {
        if (is_null($hero)) {
            return null;
        }

        return $this->globalDataService->getHeroes()->keyBy('name')[$hero]->id;
    }

    public function getGameTypeFilterValues($game_type)
    {
        if (is_array($game_type)) {
            return GameType::whereIn('short_name', $game_type)->pluck('type_id')->toArray();
        } else {
            return GameType::where('short_name', $game_type)->pluck('type_id')->first();
        }
    }

    public function getMMRTypeValue($input)
    {
        return MMRTypeID::where('name', $input)->pluck('mmr_type_id')->first();
    }
}
