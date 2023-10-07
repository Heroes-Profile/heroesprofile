<?php

namespace App\Http\Controllers\Player;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

use App\Rules\HeroInputValidation;


class PlayerHeroesController extends Controller
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
                "data" => compact('battletag', 'blizz_id', 'region'),
                "status" => "failure to validate inputs"
            ];
        }

        return view('Player.Heroes.allHeroesData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                ]);

    }
    public function showSingle(Request $request, $battletag, $blizz_id, $region, $hero){
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'hero' => ['required', new HeroInputValidation()],
        ];

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region', 'hero'), $validationRules);

        if ($validator->fails()) {
            return [
                "data" => compact('battletag', 'blizz_id', 'region', 'hero'),
                "status" => "failure to validate inputs"
            ];
        }

        return view('Player.Heroes.singleHeroData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'hero' => $hero,
                'heroObject' => $this->globalDataService->getHeroModel($hero),
                'filters' => $this->globalDataService->getFilterData(),
                'regions' => $this->globalDataService->getRegionIDtoString(),
                ]);
    }
}
