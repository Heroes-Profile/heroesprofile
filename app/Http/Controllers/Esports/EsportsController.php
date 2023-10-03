<?php

namespace App\Http\Controllers\Esports;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\NGSSeasonInputValidation;
use App\Rules\NGSDivisionInputValidation;

use App\Models\NGS\Team;

class EsportsController extends Controller
{
    public function show(Request $request){
        return view('Esports.esportsMain')  
            ->with([
                //'filters' => $this->globalDataService->getFilterData(),
                //'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                //'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                //'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                //'defaultbuildtype' => $this->globalDataService->getDefaultBuildType()
            ]);

   
    }

    public function showNGSSingleTeam(Request $request, $division, $team){
        $validationRules = [
            'division' => 'nullable|string',
            'team' => 'required|string',
        ];

        $seasonValidationRule = [
            'season' => 'nullable|numeric',
        ];

        $validator = Validator::make(compact('division', 'team'), $validationRules);

        $seasonValidator = Validator::make($request->all(), $seasonValidationRule);

        if ($validator->fails() || $seasonValidator->fails()) {
            return [
                "data" => [$request->input("season"), $division, $team],
                "status" => "failure to validate inputs"
            ];
        }

        $esport = "NGS";
        $season = $request->input("season");

        $divisions = Team::where("team_name", $team)->get();

        $transformedDivisions = $divisions->map(function ($division) {
            return [
                'code' => $division->division,
                'name' => $division->division,
            ];
        });

        return view('Esports.team')  
            ->with([
                "esport" => "NGS",
                "divisions" => $divisions,
                "division" => $division,
                "team" => $team,
                "season" => $season,
            ]);
    }


}
