<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Rules\TimeframeMinorInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\TierByIDInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\RegionInputValidation;

use App\Models\Map;
use App\Models\SeasonGameVersion;
use App\Models\GameType;
use App\Models\MMRTypeID;

class GlobalsInputValidationController extends Controller
{
    public function globalsValidationRules($timeframeType)
    {
        return [
            'timeframe_type' => 'required|in:minor,major',           
            'timeframe' => ['required', new TimeframeMinorInputValidation($timeframeType)],
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

    public function getTimeframeFilterValues($timeframeType, $timeframes){
        if($timeframeType == "major"){
            $query = SeasonGameVersion::select('game_version');

            foreach ($timeframes as $timeframe) {
                $query->orWhere('game_version', 'like', $timeframe . '%');
            }
            $gameVersion = $query->get()
                ->pluck('game_version')
                ->toArray();
            return $gameVersion;
        }

        return $timeframes;
    }

    public function getRegionFilterValues($regions)
    {
        if (is_null($regions)) {
            return null;
        }

        if(!is_array($regions)){
            return $this->globalDataService->getRegionStringToID()[$regions];
        }

        return array_map(function ($region) {
            return $this->globalDataService->getRegionStringToID()[$region];
        }, $regions);
    }

    public function getGameMapFilterValues($game_maps){
        if (is_null($game_maps)) {
            return null;
        }
        $mapIds = Map::whereIn('name', $game_maps)->pluck('map_id')->toArray();

        return $mapIds;
    }

    public function getHeroFilterValue($hero){
        if (is_null($hero)) {
            return null;
        }
        return session('heroes')->keyBy('name')[$hero]->id;
    }

    public function getGameTypeFilterValues($game_type)
    {   
        if(is_array($game_type)){
            return GameType::whereIn("short_name", $game_type)->pluck("type_id")->toArray();
        }else{
            return GameType::where("short_name", $game_type)->pluck("type_id")->first();
        }
    }

    public function getMMRTypeValue($input){
        return MMRTypeID::where("name", $input)->pluck("mmr_type_id")->first();
    }
}
