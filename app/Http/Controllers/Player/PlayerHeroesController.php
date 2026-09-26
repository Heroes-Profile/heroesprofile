<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Battletag;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        $account_level = 0;
        $account_level_data = $result = Battletag::where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->select('account_level')
            ->orderByDesc('account_level')
            ->first();

        if ($account_level_data && ! empty($account_level_data)) {
            $account_level = $account_level_data->account_level;
        }

        return view('Player.Heroes.allHeroesData')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'battletag' => $battletag,
            'account_level' => $account_level,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
        ]);

    }

    public function showSingle(Request $request, $battletag, $blizz_id, $region, $hero)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'hero' => ['required', new HeroInputValidation],
        ];

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region', 'hero'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('battletag', 'blizz_id', 'region', 'hero'),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        return view('Player.Heroes.singleHeroData')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),

            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'hero' => $hero,
            'heroObject' => $this->globalDataService->getHeroModel($hero),
            'heroes' => $this->globalDataService->getHeroes(),
            'filters' => $this->globalDataService->getFilterData(),
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
        ]);
    }
}
