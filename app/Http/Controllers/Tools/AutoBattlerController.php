<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Global\GlobalsInputValidationController;
use App\Models\HeroesDataTalent;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AutoBattlerController extends GlobalsInputValidationController
{
    public function show(Request $request)
    {
        return view('Tools.autoBattler')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'talentbuilderstyle' => $this->globalDataService->getTalentBuilderStyle(),
            ]);
    }

    public function getHeroTalents(Request $request)
    {
        $validationRules = [
            'hero' => ['required', new HeroInputValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $heroName = $request['hero'];

        $talents = Cache::remember('auto_battler_talents_'.$heroName, 3600, function () use ($heroName) {
            return HeroesDataTalent::where('hero_name', $heroName)
                ->orderBy('level', 'ASC')
                ->orderBy('sort', 'ASC')
                ->get()
                ->groupBy('level');
        });

        return response()->json(['talents' => $talents]);
    }
}
