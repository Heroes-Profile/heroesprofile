<?php

namespace App\Http\Controllers\Esports\CCL;

use App\Http\Controllers\Controller;
use App\Models\CCL\CCLTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CCLController extends Controller
{
    public function show(Request $request)
    {
        $defaultseason = CCLTeam::max('season');

        return view('Esports.CCL.cclMain')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'urlparameters' => $request->query(),
                'organizations' => CCLTeam::select('team_name')
                    ->distinct()
                    ->get()
                    ->map(function ($team) {
                        return [
                            'code' => $team->team_name,
                            'name' => $team->team_name,
                        ];
                    }),
            ]);
    }

    public function getOrganizationData(Request $request)
    {
        $validationRules = [
            'season' => 'required|in:1,2,3,4',
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

        return CCLTeam::where('season', $request['season'])->get();
    }
}
