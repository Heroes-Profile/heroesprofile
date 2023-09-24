<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\SingleGameMapInputValidation;

class PlayerMapsController extends Controller
{
    public function showAll(Request $request, $battletag, $blizz_id, $region)
    {
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.Maps.allMapData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                ]);
    }

    public function showSingle(Request $request, $battletag, $blizz_id, $region, $role){
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }
        $map = (new SingleGameMapInputValidation())->passes('map', $request["map"]);


        return view('Player.Maps.singleMapData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'map' => $map,
                'filters' => $this->globalDataService->getFilterData(),
                'regions' => $this->globalDataService->getRegionIDtoString(),
                ]);
    }
}
