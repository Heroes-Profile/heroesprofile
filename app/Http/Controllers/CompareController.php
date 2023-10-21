<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Rules\HeroInputValidation;


class CompareController extends Controller
{
    public function show(Request $request, $hero = null)
    {
        if (! is_null($hero)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation()],
            ];

            $validator = Validator::make(['hero' => $hero], $validationRules);

            if ($validator->fails()) {
                return back();
            }
        }

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('compare')
            ->with([
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
            ]);
    }

    public function getData(Request $request){
        //return response()->json($request->all());

        $validationRules = [
            'hero' => ['required', new HeroInputValidation()],
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }
    }
}
