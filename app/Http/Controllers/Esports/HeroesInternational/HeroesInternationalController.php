<?php

namespace App\Http\Controllers\Esports\HeroesInternational;

use App\Http\Controllers\Controller;
use App\Models\HeroesInternational\HeroesInternationalMainTeam;
use App\Models\HeroesInternational\HeroesInternationalNationsCupTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HeroesInternationalController extends Controller
{
    private $esport;

    public function show(Request $request)
    {
        $validationRules = [
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make($request->all(), $validationRules);

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

        $tournament = $request['tournament'];

        if ($tournament == 'main') {
            return view('Esports.HeroesInternational.heroesInternationalMain')
                ->with([
                    'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                    'heroes' => $this->globalDataService->getHeroes(),
                    'defaultseason' => 1,
                    'filters' => $this->globalDataService->getFilterData(),
                    'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                    'tournament' => 'main',
                    'urlparameters' => $request->query(),
                ]);
        } elseif ($tournament == 'nationscup') {
            return view('Esports.HeroesInternational.heroesInternationalNationsCup')
                ->with([
                    'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                    'heroes' => $this->globalDataService->getHeroes(),
                    'defaultseason' => 1,
                    'filters' => $this->globalDataService->getFilterData(),
                    'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                    'tournament' => 'nationscup',
                    'urlparameters' => $request->query(),
                ]);
        } else {
            return view('Esports.HeroesInternational.heroesInternationalEntry')->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
            ]);
        }
    }

    public function getTeamsData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'season' => 'required|in:1',
            'esport' => 'required|in:hi,hi_nc',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];
        $this->esport = $request['esport'];

        if ($this->esport == 'hi') {
            return HeroesInternationalMainTeam::where('season', $request['season'])->get();
        } elseif ($this->esport) {
            return HeroesInternationalNationsCupTeam::where('season', $request['season'])->get();
        }

    }
}
