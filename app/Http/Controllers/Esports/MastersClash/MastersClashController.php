<?php

namespace App\Http\Controllers\Esports\MastersClash;

use App\Http\Controllers\Controller;
use App\Models\MastersClash\MastersClashTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MastersClashController extends Controller
{
    public function show(Request $request)
    {
        $defaultseason = MastersClashTeam::max('season');

        return view('Esports.MastersClash.mastersClashMain')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'urlparameters' => $request->query(),
            ]);
    }

    public function getTeamsData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'season' => 'required|in:1',
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

        return MastersClashTeam::where('season', $request['season'])->get();
    }
}
