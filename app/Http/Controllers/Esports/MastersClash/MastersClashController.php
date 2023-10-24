<?php

namespace App\Http\Controllers\Esports\MastersClash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\MastersClash\MastersClashTeam;

use App\Models\MastersClash\Replay;
use App\Models\Map;

class MastersClashController extends Controller
{
    public function show(Request $request)
    {
        $defaultseason = MastersClashTeam::max('season');

        return view('Esports.MastersClash.mastersClashMain')
            ->with([
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            ]);
    }


    public function getTeamsData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => 'required|in:1',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];

        return MastersClashTeam::where('season', $request['season'])->get();
    }
    public function getRecentMatchData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => 'required|in:1',
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

        $results = Replay::select('heroesprofile_mcl.replay.replayID', 'hero', 'game_map', 'replay.team_0_name', 'replay.team_1_name', 'round', 'game', 'game_date')
            ->join('heroesprofile_mcl.player', 'heroesprofile_mcl.player.replayID', '=', 'heroesprofile_mcl.replay.replayID')
            ->join('heroesprofile_mcl.teams', 'heroesprofile_mcl.teams.team_id', '=', 'heroesprofile_mcl.player.team_name')
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
