<?php

namespace App\Http\Controllers\Esports\Other;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Esports\Other\Series;
use App\Models\Esports\Other\Replay;
use App\Models\Esports\Other\Regions;
use Illuminate\Support\Facades\Validator;

class EsportOtherController extends Controller
{
    public function show(Request $request)
    {
        return view('Esports.Other.OtherMain')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'series' => Series::whereNot('name', 'Nut Cup')->whereNot('name', 'Heroes Lounge')->get(),
            ]);
    }

    public function showSeries(Request $request, $series)
    {
        $validationRules = [
            'series' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Series::where('name', $value)->exists()) {
                        $fail("The selected $attribute is invalid.");
                    }
                },
            ],
        ];
        
        $validator = Validator::make(compact('series'), $validationRules);
        
        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                    'errors' => $validator->errors(),
                ];
            }
        }



        $seasons = Replay::select('season')
        ->where('series', $series)
        ->distinct()
        ->orderBy('season', 'desc')
        ->get()
        ->map(function ($season) {
            return [
                'code' => $season->season,  
                'name' => $season->season,
            ];
        });

        $regions = Replay::select('heroesprofile_ml.regions.name as name', 'heroesprofile_ml.regions.region_id as code')
            ->distinct()
            ->join('heroesprofile_ml.regions', 'heroesprofile_ml.regions.region_id', '=', 'heroesprofile_ml.replay.region')  
            ->orderBy('code', 'asc')
            ->where('series', $series) 
            ->get();

        $tournaments = Replay::select('tournament as name', 'tournament as code')
            ->distinct()
            ->orderBy('name', 'asc')
            ->where('series', $series) 
            ->get();


        return view('Esports.Other.OtherSeries')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'series' => Series::where("name", $series)->first(),
                'seasons' => $seasons,
                'regions' => $regions,
                'tournaments' => $tournaments,
            ]);
    }

    public function getTeamData(Request $request){

        $validationRules = [
            'series' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Series::where('name', $value)->exists()) {
                        $fail("The selected $attribute is invalid.");
                    }
                },
            ],
            'season' => 'nullable|integer',
            'region' => 'nullable|integer',
            'tournament' => 'nullable|string',
        ];
        
        
        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return response()->json([
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                    'errors' => $validator->errors(),
                ]);
            }
        }

        $series = $request["series"];
        $region = $request["region"];
        $season = $request["season"];
        $tournament = $request["tournament"];

        $teams = Replay::selectRaw('team_0_name as team_name')
        ->where("series", $series)
        ->when($region, function ($query) use ($region) {
            return $query->where('region', $region);  
        })
        ->when($tournament, function ($query) use ($tournament) {
            return $query->where('tournament', $tournament);  
        })
        ->when($season, function ($query) use ($season) {
            return $query->where('season', $season);  
        })
        ->union(
            Replay::selectRaw('team_1_name as team_name')
                ->where("series", $series)
                ->when($region, function ($query) use ($region) {
                    return $query->where('region', $region);  
                })
                ->when($tournament, function ($query) use ($tournament) {
                    return $query->where('tournament', $tournament);  
                })
                ->when($season, function ($query) use ($season) {
                    return $query->where('season', $season);  
                })
        )
        ->distinct()
        ->orderBy('team_name', 'asc')
        ->pluck('team_name');
    

        return $teams;
    }

    public function showSingleTeam(Request $request, $series, $team)
    {
        $validationRules = [
            'series' => 'required',
            'team' => 'required|string',
        ];

        $otherValidationRules = [
            'season' => 'nullable|numeric',
            'region' => 'nullable|numeric',
            'tournament' => 'nullable|string',
        ];

        $validator = Validator::make(compact('series', 'team'), $validationRules);

        $otherValidator = Validator::make($request->all(), $otherValidationRules);

        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        return view('Esports.team')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'esport' => 'Other',
                'series' => $series,
                'team' => $team,
                'season' => $request['season'],
                'region' => $request['region'],
                'tournament' => $request['tournament'],
                'image' => 'logo.png',
                'division' => null,
            ]);
    }

}
