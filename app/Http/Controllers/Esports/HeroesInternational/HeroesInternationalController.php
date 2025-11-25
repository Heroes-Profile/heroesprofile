<?php

namespace App\Http\Controllers\Esports\HeroesInternational;

use App\Http\Controllers\Controller;
use App\Models\HeroesInternational\HeroesInternationalMainTeam;
use App\Models\HeroesInternational\HeroesInternationalNationsCupTeam;
use App\Models\Map;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function getRecentMatchData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'season' => 'required|in:1',
            'esport' => 'required|in:hi,hi_nc',
            'pagination_page' => 'required:integer',
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
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

        $pagination_page = $request['pagination_page'];
        $perPage = 1000;

        $this->schema = 'heroesprofile_'.$this->esport;

        $results = DB::table($this->schema.'.replay')->select($this->schema.'.replay.replayID', 'hero', 'game_map', 'replay.team_0_id', 'replay.team_1_id', 'round', 'game', 'game_date')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', $this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id')
            ->orderBy('game_date', 'DESC')
            ->where('replay.season', $season)
            ->paginate($perPage, ['*'], 'page', $pagination_page);

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');
        $hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;

        $paginationInfo = [
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total' => $results->total(),
            'last_page' => $results->lastPage(),
        ];

        $groupedResults = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps, $hero) {
            $heroes = [];

            $foundHero = false;

            for ($i = 0; $i < 10; $i++) {
                $heroes[$i] = isset($group[$i]) && isset($group[$i]->hero) ? $heroData[$group[$i]->hero] : null;

                if ($hero && isset($group[$i]) && isset($group[$i]->hero) && $heroes[$i]['id'] == $hero) {
                    $foundHero = true;
                }
            }

            if ($hero && ! $foundHero) {
                return null;
            }

            return [
                'replayID' => $group[0]->replayID,
                'game_date' => $group[0]->game_date,
                'game_map' => isset($maps[$group[0]->game_map]) ? $maps[$group[0]->game_map] : null,
                'game' => $group[0]->game,
                'round' => $group[0]->round,
                'team_0_id' => $group[0]->team_0_id,
                'team_1_id' => $group[0]->team_1_id,
                'heroes' => $heroes,
            ];
        })->filter()->values()->all();

        return ['data' => $groupedResults, 'pagination' => $paginationInfo];
    }
}
