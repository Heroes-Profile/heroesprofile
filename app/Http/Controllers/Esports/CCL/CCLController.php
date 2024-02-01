<?php

namespace App\Http\Controllers\Esports\CCL;

use App\Http\Controllers\Controller;
use App\Models\CCL\CCLTeam;
use App\Models\CCL\Replay;
use App\Models\Map;
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
            ]);
    }

    public function getOrganizationData(Request $request)
    {
        $validationRules = [
            'season' => 'required|in:1,2,3,4',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        return CCLTeam::where('season', $request['season'])->get();
    }

    public function getRecentMatchData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => 'required|in:1,2,3,4',
            'pagination_page' => 'required:integer',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];

        $pagination_page = $request['pagination_page'];
        $perPage = 1000;

        $results = Replay::select('heroesprofile_ccl.replay.replayID', 'hero', 'game_map', 'replay.team_0_id', 'replay.team_1_id', 'round', 'game', 'game_date')
            ->join('heroesprofile_ccl.player', 'heroesprofile_ccl.player.replayID', '=', 'heroesprofile_ccl.replay.replayID')
            ->join('heroesprofile_ccl.teams', 'heroesprofile_ccl.teams.team_id', '=', 'heroesprofile_ccl.player.team_id')
            ->orderBy('game_date', 'DESC')
            ->where('replay.season', $season)
            ->paginate($perPage, ['*'], 'page', $pagination_page);

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $paginationInfo = [
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total' => $results->total(),
            'last_page' => $results->lastPage(),
        ];

        $groupedResults = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps) {
            $heroes = [];

            for ($i = 0; $i < 10; $i++) {
                $heroes[$i] = isset($group[$i]) && isset($group[$i]['hero']) ? $heroData[$group[$i]['hero']] : null;
            }

            return [
                'replayID' => $group[0]['replayID'],
                'game_date' => $group[0]['game_date'],
                'game_map' => isset($maps[$group[0]['game_map']]) ? $maps[$group[0]['game_map']] : null,
                'game' => $group[0]['game'],
                'round' => $group[0]['round'],
                'team_0_name' => $group[0]['team_0_name'],
                'team_1_name' => $group[0]['team_1_name'],
                'heroes' => $heroes,
            ];
        })->values()->all();

        return ['data' => $groupedResults, 'pagination' => $paginationInfo];
    }
}
