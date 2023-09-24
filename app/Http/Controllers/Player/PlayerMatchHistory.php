<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlayerMatchHistory extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);

        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.matchHistory')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                ]);
    }
}
