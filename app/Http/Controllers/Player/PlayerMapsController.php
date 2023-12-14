<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Battletag;
use App\Models\Map;
use App\Rules\GameMapInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerMapsController extends Controller
{
    public function showAll(Request $request, $battletag, $blizz_id, $region)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('battletag', 'blizz_id', 'region'),
                'status' => 'failure to validate inputs',
            ];
        }

        $account_level = 0;
        $account_level_data = $result = Battletag::where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->select('account_level')
            ->orderByDesc('account_level')
            ->first();

        if($account_level_data && !empty($account_level_data)){
            $account_level = $account_level_data->account_level;
        }

        return view('Player.Maps.allMapData')->with([
            'regions' => $this->globalDataService->getRegionIDtoString(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'account_level' => $account_level,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault("multi"),

        ]);
    }

    public function showSingle(Request $request, $battletag, $blizz_id, $region, $map)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'map' => ['required', new GameMapInputValidation()],
        ];

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region', 'map'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('battletag', 'blizz_id', 'region', 'map'),
                'status' => 'failure to validate inputs',
            ];
        }

        $map = $request['map'];
        $mapobject = Map::where('name', $map)->first();

        return view('Player.Maps.singleMapData')->with([
            'regions' => $this->globalDataService->getRegionIDtoString(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'map' => $map,
            'mapobject' => $mapobject,
            'filters' => $this->globalDataService->getFilterData(),
            'regions' => $this->globalDataService->getRegionIDtoString(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault("single"),

        ]);
    }
}
