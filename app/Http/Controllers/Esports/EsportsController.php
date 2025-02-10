<?php

namespace App\Http\Controllers\Esports;

use App\Http\Controllers\Controller;
use App\Models\CCL\CCLTeam;
use App\Models\HeroesDataTalent;
use App\Models\HeroesInternational\HeroesInternationalMainTeam;
use App\Models\HeroesInternational\HeroesInternationalNationsCupTeam;
use App\Models\Map;
use App\Models\NGS\NGSTeam;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\NGSDivisionInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Esports\Other\OtherTeam;
use App\Models\Esports\Other\Series;

class EsportsController extends Controller
{
    private $esport;

    private $schema;

    private $battletag;

    private $blizz_id;

    private $season;

    private $division;

    private $team;

    private $team_name;

    private $hero;

    private $game_map;

    private $tournament;

    private $series;

    public function show(Request $request)
    {
        return view('Esports.esportsMain')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            ]);
    }

    public function showPlayerMatchHistory(Request $request, $esport, $battletag, $blizz_id)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,NutCup,HeroesInternational',
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
        ];

        $otherValidationRules = [
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id'), $validationRules);
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

        return view('Esports.singlePlayerMatchHistory')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'esport' => $esport,
            'filters' => $this->globalDataService->getFilterData(),
            'season' => $request['season'],
        ]);
    }

    public function showSingleTeam(Request $request, $esport, $team)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,NutCup,HeroesInternational',
            'secondary' => 'nullable|in:Main,NationsCup',
            'team' => 'required|string',
        ];

        $otherValidationRules = [
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'team'), $validationRules);

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

        $image = '';
        if ($esport == 'NGS') {
            $image = NGSTeam::select('image')->where('team_name', $team)->first();

            if ($image) {
                $image = $image->image;
            }
        }

        return view('Esports.team')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'esport' => $esport,
                'series' => 'null',
                'region' => 'null',
                'team' => $team,
                'season' => $request['season'],
                'division' => $request['division'],
                'tournament' => $request['tournament'],
                'image' => $image,
            ]);
    }

    public function showPlayer(Request $request, $esport, $battletag, $blizz_id)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'battletag' => 'required|string',
            'blizz_id' => 'required|numeric',
        ];

        $otherValidationRules = [
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id'), $validationRules);

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

        return view('Esports.singlePlayer')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'esport' => $esport,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => $request['division'],
                'tournament' => $request['tournament'],
            ]);
    }

    public function showPlayerHero(Request $request, $esport, $battletag, $blizz_id, $hero)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'battletag' => 'required|string',
            'blizz_id' => 'required|numeric',
            'hero' => ['required', new HeroInputValidation],
        ];

        $otherValidationRules = [
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id', 'hero'), $validationRules);

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

        $hero = $this->globalDataService->getHeroModel($request['hero']);

        return view('Esports.singlePlayerHero')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'esport' => $esport,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => $request['division'],
                'hero' => $hero,
                'tournament' => $request['tournament'],
            ]);
    }

    public function showPlayerMap(Request $request, $esport, $battletag, $blizz_id, $game_map)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'battletag' => 'required|string',
            'blizz_id' => 'required|numeric',
            'game_map' => ['required', new GameMapInputValidation],
        ];

        $otherValidationRules = [
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id', 'game_map'), $validationRules);

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
        $mapobject = Map::where('name', $request['game_map'])->first();

        return view('Esports.singlePlayerMap')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'esport' => $esport,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => $request['division'],
                'game_map' => $mapobject,
                'tournament' => $request['tournament'],
            ]);
    }

    public function showTeamMatchHistory(Request $request, $esport, $team)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
        ];

        $otherValidationRules = [
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'team'), $validationRules);
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

        return view('Esports.teamMatchHistory')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'esport' => $esport,
                'team' => $team,
                'season' => $request['season'],
                'division' => $request['division'],
                'tournament' => $request['tournament'],
                'type' => 'team',
            ]);
    }

    public function getDataSinglePlayerMatchHistory(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'team' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'pagination_page' => 'required:integer',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => [$request->all()],
                'status' => 'failure to validate inputs',
            ];
        }

        $this->esport = $request['esport'];
        $this->schema = 'heroesprofile';

        $this->blizz_id = $request['blizz_id'];
        $this->battletag = $request['battletag'];

        $this->season = $request['season'];

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        $pagination_page = $request['pagination_page'];
        $perPage = 100;

        $result = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.talents', function ($join) {
                $join->on($this->schema.'.talents.replayID', '=', $this->schema.'.replay.replayID')
                    ->on($this->schema.'.talents.battletag', '=', $this->schema.'.player.battletag');
            })
            ->join('heroes', 'heroes.id', '=', 'player.hero')
            ->select([
                'replay.replayID AS replayID',
                'replay.game_date as game_date',
                'replay.game_map AS game_map',
                'player.winner AS winner',
                'player.hero AS hero',
                'heroes.new_role as role',
                'talents.level_one AS level_one',
                'talents.level_four AS level_four',
                'talents.level_seven AS level_seven',
                'talents.level_ten AS level_ten',
                'talents.level_thirteen AS level_thirteen',
                'talents.level_sixteen AS level_sixteen',
                'talents.level_twenty AS level_twenty',
            ])
            ->where('blizz_id', $this->blizz_id)
            ->when(! is_null($this->season), function ($query) {
                $seasonDate = SeasonDate::find($this->season);
                if ($seasonDate) {
                    return $query->where('game_date', '>=', $seasonDate->start_date)
                        ->where('game_date', '<', $seasonDate->end_date);
                }

                return $query;
            })
            ->orderByDesc('game_date')
            ->paginate($perPage, ['*'], 'page', $pagination_page);

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $modifiedResult = $result->map(function ($item) use ($heroData, $talentData, $maps) {
            $item->hero_id = $item->hero;
            $item->hero = $heroData[$item->hero];

            $item->game_map = $maps[$item->game_map]['name'];

            if ($item->level_one) {
                if ($item->level_one != 0) {
                    $item->level_one = $talentData->has($item->level_one) ? $talentData[$item->level_one] : null;
                }
            }

            if ($item->level_four) {
                if ($item->level_four != 0) {
                    $item->level_four = $talentData->has($item->level_four) ? $talentData[$item->level_four] : null;
                }
            }

            if ($item->level_seven) {
                if ($item->level_seven != 0) {
                    $item->level_seven = $talentData->has($item->level_seven) ? $talentData[$item->level_seven] : null;
                }
            }

            if ($item->level_ten) {
                if ($item->level_ten != 0) {
                    $item->level_ten = $talentData->has($item->level_ten) ? $talentData[$item->level_ten] : null;
                }
            }

            if ($item->level_thirteen) {
                if ($item->level_thirteen != 0) {
                    $item->level_thirteen = $talentData->has($item->level_thirteen) ? $talentData[$item->level_thirteen] : null;
                }
            }

            if ($item->level_sixteen) {
                if ($item->level_sixteen != 0) {
                    $item->level_sixteen = $talentData->has($item->level_sixteen) ? $talentData[$item->level_sixteen] : null;
                }
            }

            if ($item->level_twenty) {
                if ($item->level_twenty != 0) {
                    $item->level_twenty = $talentData->has($item->level_twenty) ? $talentData[$item->level_twenty] : null;
                }
            }

            $item->winner = $item->winner == 1 ? 'True' : 'False';

            return $item;
        });

        return $result;
    }

    public function getTeamMatchHistoryData(Request $request)
    {

        //return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'team' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'pagination_page' => 'required:integer',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => [$request->all()],
                'status' => 'failure to validate inputs',
            ];
        }

        $this->esport = $request['esport'];
        $this->schema = 'heroesprofile';

        $this->season = $request['season'];

        $tournament = $request['tournament'];

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        if ($request['tournament'] == 'main') {
            $this->schema = 'heroesprofile_hi';
        } elseif ($request['tournament'] == 'nationscup') {
            $this->schema = 'heroesprofile_hi_nc';
        }

        $team = $request['team'];
        $team_id = null;
        $division = $request['division'];

        $pagination_page = $request['pagination_page'];
        $perPage = 100;

        $result = DB::table($this->schema.'.replay')
            ->when($this->season, function ($query) {
                $query->where(function ($query) {
                    $query->where('season', $this->season);
                });
            })
            ->when($this->esport === 'NGS' && $division, function ($query) use ($division) {
                $query->where(function ($query) use ($division) {
                    $query->where('division_0', $division)
                        ->orWhere('division_1', $division);
                });
            })
            ->where(function ($query) use ($team) {
                if ($this->esport === 'NGS' || $this->esport === 'MastersClash') {
                    $query->where('team_0_name', $team)
                        ->orWhere('team_1_name', $team);
                } elseif ($this->esport === 'CCL' || $this->esport === 'HeroesInternational') {
                    $query->where('team_0_id', $team)
                        ->orWhere('team_1_id', $team);
                }

            })
            ->orderByDesc('game_date')
            ->paginate($perPage, ['*'], 'page', $pagination_page);

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $modifiedResult = $result->map(function ($item) use ($maps, $team) {

            $item->game_map = $maps[$item->game_map]['name'];
            if ($this->esport === 'NGS' || $this->esport === 'MastersClash') {
                $item->enemy = $team == $item->team_0_name ? $item->team_1_name : $item->team_0_name;
            } elseif ($this->esport === 'CCL' || $this->esport === 'HeroesInternational') {
                $item->enemy = $team == $item->team_0_id ? $item->team_1_id : $item->team_0_id;
            }

            return $item;
        });

        return $result;
    }

    public function getRecentMatchData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'team' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'game_map' => ['sometimes', 'nullable',  new GameMapInputValidation],
            'pagination_page' => 'required:integer',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => [$request->all()],
                'status' => 'failure to validate inputs',
            ];
        }

        $this->esport = $request['esport'];
        $this->schema = 'heroesprofile';

        $this->blizz_id = $request['blizz_id'];
        $this->battletag = $request['battletag'];
        $this->team = $request['team'];

        $this->division = $request['division'];
        $this->season = $request['season'];
        $this->hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;
        $this->game_map = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id')->toArray() : null;

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        $pagination_page = $request['pagination_page'];
        $perPage = 1000;

        $results = DB::table($this->schema.'.replay')->select($this->schema.'.replay.replayID', 'hero', 'game_map', 'round', 'game', 'game_date')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', function ($join) {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_name');
                } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
                }
            })
            ->when($this->esport == 'NGS' || $this->esport == 'MastersClash', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                    $this->schema.'.player.team_name',
                ]);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_id',
                    $this->schema.'.replay.team_1_id',
                    $this->schema.'.player.team_id',
                ]);
            })
            ->orderBy('game_date', 'DESC')
            ->where('replay.season', $this->season)
            ->when(! is_null($this->division), function ($query) {
                return $query->where($this->schema.'.teams.division', $this->division);
            })
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
                $heroes[$i] = isset($group[$i]) && isset($group[$i]->hero) ? $heroData[$group[$i]->hero] : null;
            }

            $team_0_name = null;
            $team_1_name = null;

            if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                $team_0_name = $group[0]->team_0_name;
                $team_1_name = $group[0]->team_1_name;
            } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc') {
                $team_0_name = $group[0]->team_0_id;
                $team_1_name = $group[0]->team_1_id;
            }

            return [
                'replayID' => $group[0]->replayID,
                'game_date' => $group[0]->game_date,
                'game_map' => isset($maps[$group[0]->game_map]) ? $maps[$group[0]->game_map] : null,
                'game' => $group[0]->game,
                'round' => $group[0]->round,
                'team_0_name' => $team_0_name,
                'team_1_name' => $team_1_name,
                'heroes' => $heroes,
            ];
        })->values()->all();

        return ['data' => $groupedResults, 'pagination' => $paginationInfo];
    }

    public function getOverallHeroStats(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,NutCup,hi,hi_nc',
            'season' => 'required|numeric',
            'division' => ['sometimes', 'nullable', new NGSDivisionInputValidation],
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $this->season = $request['season'];
        $this->division = $request['division'];

        $this->esport = $request['esport'];
        $this->schema = 'heroesprofile';

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        $heroResults = DB::table($this->schema.'.replay')->select('replay.replayID', 'hero', 'winner')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->where('replay.season', $this->season)
            ->when(! is_null($this->division), function ($query) {
                return $query->where($this->schema.'.replay.division_0', $this->division)->orWhere($this->schema.'.replay.division_1', $this->division);
            })
            ->get();

        $banResults = DB::table($this->schema.'.replay_bans')->whereIn('replayID', $heroResults->pluck('replayID')->toArray())
            ->groupBy('hero')
            ->select('hero')
            ->selectRaw('COUNT(*) as ban_count')
            ->get();

        $totalBanCount = $banResults->sum('ban_count') / 6;

        $banResults = $banResults->pluck('ban_count', 'hero')->toArray();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $totalGames = $heroResults->count() / 10;

        $groupedResults = $heroResults->groupBy('hero')->map(function ($group) use ($banResults, $totalBanCount, $heroData, $totalGames) {
            $wins = $group->sum('winner');
            $losses = $group->count() - $wins;
            $gamesPlayed = $wins + $losses;

            $bans = array_key_exists($group->first()->hero, $banResults) && $banResults[$group->first()->hero] > 0 ? $banResults[$group->first()->hero] : 0;

            return [
                'hero' => $heroData[$group->first()->hero],
                'hero_id' => $group->first()->hero,
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'ban_rate' => $bans > 0 ? round(($bans / $totalBanCount) * 100, 2) : 0,
                'bans' => $bans,
                'popularity' => round((($gamesPlayed + $bans) / $totalGames) * 100, 2),
            ];
        })
            ->sortByDesc('win_rate')
            ->values()
            ->all();

        return $groupedResults;
    }

    public function getOverallTalentStats(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,NutCup,hi,hi_nc',
            'season' => 'required|numeric',
            'division' => ['sometimes', 'nullable', new NGSDivisionInputValidation],
            'hero' => ['required', new HeroInputValidation],
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $this->season = $request['season'];
        $this->division = $request['division'];
        $hero = $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id;

        $this->esport = $request['esport'];
        $this->schema = 'heroesprofile';

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        $result = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.talents', function ($join) {
                $join->on($this->schema.'.talents.replayID', '=', $this->schema.'.replay.replayID')
                    ->on($this->schema.'.talents.battletag', '=', $this->schema.'.player.battletag');
            })
            ->select([
                $this->schema.'.player.winner AS winner',
                $this->schema.'.player.hero AS hero',
                $this->schema.'.talents.level_one AS level_one',
                $this->schema.'.talents.level_four AS level_four',
                $this->schema.'.talents.level_seven AS level_seven',
                $this->schema.'.talents.level_ten AS level_ten',
                $this->schema.'.talents.level_thirteen AS level_thirteen',
                $this->schema.'.talents.level_sixteen AS level_sixteen',
                $this->schema.'.talents.level_twenty AS level_twenty',
            ])
            ->where('season', $this->season)
            ->when(! is_null($this->division), function ($query) {
                return $query->where($this->schema.'.replay.division_0', $this->division)->orWhere($this->schema.'.replay.division_1', $this->division);
            })
            ->where('hero', $hero)
            //->toSql();
            ->get();
        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        return [
            'talentData' => $this->getHeroTalentData($result, $talentData),
            'buildData' => $this->getHeroTalentBuildData($result, $heroData[$hero], $talentData),
        ];

    }

    private function getHeroTalentData($result, $talentData)
    {
        $returnData = [];
        $levelKeys = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

        $resultCollection = collect($result);

        $resultCollection->each(function ($data) use (&$returnData, $levelKeys, $talentData) {
            foreach ($levelKeys as $levelKey) {
                if ($data->$levelKey == 0 || ! $talentData->has($data->$levelKey)) {
                    continue;
                }

                $key = $data->$levelKey;

                if (! isset($returnData[$key])) {
                    $returnData[$key] = ['wins' => 0, 'losses' => 0];
                }

                $returnData[$key][$data->winner == 1 ? 'wins' : 'losses']++;
                $returnData[$key]['talent'] = $talentData[$data->$levelKey];
            }
        });

        $formattedData = [];

        $levelTotals = [];

        foreach ($returnData as $data) {
            if (! array_key_exists($data['talent']['level'], $levelTotals)) {
                $levelTotals[$data['talent']['level']] = 0;
            }
            $levelTotals[$data['talent']['level']] += $data['wins'] + $data['losses'];
        }

        foreach ($returnData as $data) {
            $level = $data['talent']['level'];
            $sort = ($data['talent']['sort'] - 1);

            if (! array_key_exists($level, $formattedData)) {
                $formattedData[$level] = [];
            }

            if (! array_key_exists($sort, $formattedData[$level])) {
                $formattedData[$level][$sort] = [];
            }

            $formattedData[$level][$sort] = [
                'win_rate' => ($data['wins'] + $data['losses']) > 0 ? round(($data['wins'] / ($data['wins'] + $data['losses'])) * 100, 2) : 0,
                'wins' => $data['wins'],
                'losses' => $data['losses'],
                'sort' => $data['talent']['sort'],
                'popularity' => round((($data['losses'] + $data['wins']) / $levelTotals[$data['talent']['level']]) * 100, 2),
                'games_played' => $data['wins'] + $data['losses'],
                'talentInfo' => $data['talent'],
            ];

        }

        return $formattedData;
    }

    private function getHeroTalentBuildData($result, $hero, $talentData)
    {
        $returnData = [];

        foreach ($result as $replay) {
            $level_one = $replay->level_one;
            $level_four = $replay->level_four;
            $level_seven = $replay->level_seven;
            $level_ten = $replay->level_ten;
            $level_thirteen = $replay->level_thirteen;
            $level_sixteen = $replay->level_sixteen;
            $level_twenty = $replay->level_twenty;

            if ($level_one == 0 || $level_four == 0 || $level_seven == 0 || $level_ten == 0 || $level_thirteen == 0 || $level_sixteen == 0 || $level_twenty == 0) {
                continue;
            } elseif (! $talentData->has($level_one) || ! $talentData->has($level_four) || ! $talentData->has($level_seven) || ! $talentData->has($level_ten) || ! $talentData->has($level_thirteen) || ! $talentData->has($level_sixteen) || ! $talentData->has($level_twenty)) {
                continue;
            }
            $key = $level_one.'|'.$level_four.'|'.$level_seven.'|'.$level_ten.'|'.$level_thirteen.'|'.$level_sixteen.'|'.$level_twenty;
            if (! array_key_exists($key, $returnData)) {
                $returnData[$key] = [];
                $returnData[$key]['wins'] = 0;
                $returnData[$key]['losses'] = 0;
                $returnData[$key]['games_played'] = 0;

                $returnData[$key]['level_one'] = $replay->level_one;
                $returnData[$key]['level_four'] = $replay->level_four;
                $returnData[$key]['level_seven'] = $replay->level_seven;
                $returnData[$key]['level_ten'] = $replay->level_ten;
                $returnData[$key]['level_thirteen'] = $replay->level_thirteen;
                $returnData[$key]['level_sixteen'] = $replay->level_sixteen;
                $returnData[$key]['level_twenty'] = $replay->level_twenty;
            }

            if ($replay->winner == 1) {
                $returnData[$key]['wins']++;
            } else {
                $returnData[$key]['losses']++;
            }
            $returnData[$key]['games_played']++;

        }

        usort($returnData, function ($a, $b) {
            return $b['games_played'] - $a['games_played'];
        });

        $returnData = array_slice($returnData, 0, 7);

        foreach ($returnData as &$data) {
            foreach ($result as $replay) {
                $level_one = $replay->level_one;
                $level_four = $replay->level_four;
                $level_seven = $replay->level_seven;
                $level_ten = $replay->level_ten;
                $level_thirteen = $replay->level_thirteen;
                $level_sixteen = $replay->level_sixteen;
                $level_twenty = $replay->level_twenty;

                if ($data['level_one'] != $level_one || $data['level_four'] != $level_four || $data['level_seven'] != $level_seven || $data['level_ten'] != $level_ten || $level_twenty != 0) {
                    continue;
                }

                if ($replay->winner == 1) {
                    $data['wins']++;

                } else {
                    $data['losses']++;
                }
                $data['games_played']++;
            }
        }
        foreach ($returnData as &$data) {
            $data['level_one'] = $talentData[$data['level_one']];
            $data['level_four'] = $talentData[$data['level_four']];
            $data['level_seven'] = $talentData[$data['level_seven']];
            $data['level_ten'] = $talentData[$data['level_ten']];
            $data['level_thirteen'] = $talentData[$data['level_thirteen']];
            $data['level_sixteen'] = $talentData[$data['level_sixteen']];
            $data['level_twenty'] = $talentData[$data['level_twenty']];
            $data['win_rate'] = round(($data['wins'] / $data['games_played']) * 100, 2);
            $data['hero'] = $hero;
        }

        return $returnData;
    }

    public function getData(Request $request)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational,Other',
            'tournament' => 'nullable|in:main,nationscup',
            'team' => 'nullable|string',
            'series' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'division' => 'nullable|string',
            'series' => 'nullable|string',
            'season' => 'nullable|numeric',
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'game_map' => ['sometimes', 'nullable',  new GameMapInputValidation],
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => [$request->all()],
                'status' => 'failure to validate inputs',
            ];
        }

        $this->esport = $request['esport'];
        $this->tournament = $request['tournament'];

        if ($request['tournament'] == 'main') {
            $this->esport = 'hi';
        } elseif ($request['tournament'] == 'nationscup') {
            $this->esport = 'hi_nc';
        }

        $this->schema = 'heroesprofile';

        $this->blizz_id = $request['blizz_id'];
        $this->battletag = $request['battletag'];
        $this->team = $request['team'];

        $this->division = $request['division'];
        $this->season = $request['season'];
        $this->tournament = $request['tournament'];

        $this->hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;
        $this->game_map = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id')->toArray() : null;

        $this->series = $request['series'];

        if ($this->esport == 'CCL') {
            $this->team = $request['team'] ? CCLTeam::when(! is_null($this->season), function ($query) {
                return $query->where('season', $this->season);
            })->where('team_name', $request['team'])->first()->team_id
            : null;

            $this->team_name = $request['team'];
        } elseif ($this->esport == 'hi' && $this->tournament == 'main') {
            $this->team = $request['team'] ? HeroesInternationalMainTeam::where('season', $this->season)->where('team_name', $request['team'])->first()->team_id : null;
            $this->team_name = $request['team'];
        } elseif ($this->esport == 'hi_nc' && $this->tournament == 'nationscup') {

            $this->team = $request['team'] ? HeroesInternationalNationsCupTeam::where('season', 1)->where('team_name', $request['team'])->first()->team_id : null;
            $this->team_name = $request['team'];
        }else if($this->esport == 'Other'){
            $this->team = OtherTeam::select("team_id")->where("team_name", $request["team"])->first()->team_id;
            $this->team_name = $request['team'];
        }

        if($this->esport == 'Other'){
            $this->schema .= '_ml';
        }else if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport == 'HeroesInternational' && $this->tournament == 'main') {
            $this->schema .= '_hi';
        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        $results = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
            ->join($this->schema.'.scores', function ($join) {
                $join->on($this->schema.'.scores.replayID', '=', $this->schema.'.replay.replayID')
                    ->on($this->schema.'.scores.battletag', '=', $this->schema.'.player.battletag');
            })
            ->join($this->schema.'.talents', function ($join) {
                $join->on($this->schema.'.talents.replayID', '=', $this->schema.'.replay.replayID')
                    ->on($this->schema.'.talents.battletag', '=', $this->schema.'.player.battletag');
            })
            ->join($this->schema.'.teams', function ($join) {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_name');
                } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc' || $this->esport == 'Other') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
                }
            })
            ->join('heroesprofile.heroes', 'heroesprofile.heroes.id', '=', $this->schema.'.player.hero')
            ->select([
                $this->schema.'.teams.team_name',
                $this->schema.'.teams.image',
                $this->schema.'.battletags.battletag',
                $this->schema.'.battletags.blizz_id',
                $this->schema.'.replay.replayID',
                $this->schema.'.replay.game_date',
                $this->schema.'.replay.game_map',
                $this->schema.'.replay.game',
                $this->schema.'.replay.round',
                'heroes.new_role',
                $this->schema.'.player.winner',
                $this->schema.'.player.blizz_id',
                $this->schema.'.player.hero',
                $this->schema.'.player.team',
                $this->schema.'.player.hero_level',
                $this->schema.'.player.mastery_tier as mastery_taunt',
                $this->schema.'.scores.kills',
                $this->schema.'.scores.assists',
                $this->schema.'.scores.takedowns',
                $this->schema.'.scores.time_spent_dead',
                $this->schema.'.scores.deaths',
                $this->schema.'.talents.level_one AS level_one',
                $this->schema.'.talents.level_four AS level_four',
                $this->schema.'.talents.level_seven AS level_seven',
                $this->schema.'.talents.level_ten AS level_ten',
                $this->schema.'.talents.level_thirteen AS level_thirteen',
                $this->schema.'.talents.level_sixteen AS level_sixteen',
                $this->schema.'.talents.level_twenty AS level_twenty',

            ])
            ->when($this->esport == 'NGS' || $this->esport == 'MastersClash', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                    $this->schema.'.player.team_name',
                    $this->schema.'.replay.team_0_map_ban',
                    $this->schema.'.replay.team_0_map_ban_2',
                    $this->schema.'.replay.team_1_map_ban',
                    $this->schema.'.replay.team_1_map_ban_2',
                ]);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_id',
                    $this->schema.'.replay.team_1_id',
                    $this->schema.'.player.team_id',
                    $this->schema.'.replay.team_0_map_ban',
                    $this->schema.'.replay.team_0_map_ban_2',
                    $this->schema.'.replay.team_1_map_ban',
                    $this->schema.'.replay.team_1_map_ban_2',
                ]);
            })
            ->when($this->esport == 'Other', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                    $this->schema.'.player.team_id'
                ]);
            })
            ->when(! is_null($this->game_map), function ($query) {
                return $query->where($this->schema.'.replay.game_map', $this->game_map);
            })
            ->when(! is_null($this->team) && ($this->esport == 'NGS' || $this->esport == 'MastersClash'), function ($query) {
                return $query->where($this->schema.'.teams.team_name', $this->team);
            })
            ->when(! is_null($this->team) && ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc'|| $this->esport == 'Other'), function ($query) {
                return $query->where($this->schema.'.teams.team_id', $this->team);
            })
            ->when(! is_null($this->blizz_id), function ($query) {
                return $query->where($this->schema.'.player.blizz_id', $this->blizz_id);
            })
            ->when(! is_null($this->hero), function ($query) {
                return $query->where($this->schema.'.player.hero', $this->hero);
            })
            ->when(! is_null($this->division), function ($query) {
                return $query->where($this->schema.'.teams.division', $this->division);
            })
            ->when(! is_null($this->season), function ($query) {
                return $query->where($this->schema.'.teams.season', $this->season);
            })
        //->toSql();
            ->get();
        //return $results;

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');




        $replaysLost = $results->filter(function ($result) {
            return $result->winner === 0;
        });

        $replaysWon = $results->filter(function ($result) {
            return $result->winner === 1;
        });

        $topEnemyHeroes = $this->getTopEnemyAlly($replaysLost->pluck('replayID')->toArray(), $this->team, $heroData, 0);
        $topAllyHeroes = $this->getTopEnemyAlly($replaysWon->pluck('replayID')->toArray(), $this->team, $heroData, 1);

        $teamData = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', function ($join) {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_name');
                } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc' || $this->esport == 'Other') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
                }
            })
            ->select([
                $this->schema.'.teams.team_name',
                $this->schema.'.teams.image',
            ])
            ->whereIn($this->schema.'.replay.replayID', $results->pluck('replayID')->toArray())
            ->get();

        
        $teamImageMap = [];

        foreach ($teamData as $result) {
            $teamImageMap[$result->team_name] = $result->image;
        }

        $topEnemyTeams = $this->getTopEnemyTeams($replaysLost->pluck('replayID')->toArray(), $this->team, $teamImageMap, $this->series);


        $players = $results->groupBy('battletag')->map(function ($playerResults, $battletag) use ($heroData) {
            $totalGames = $playerResults->count();
            $mostPlayedHero = $playerResults->groupBy('hero')->sortDesc()->keys()->first();

            $totalGamesOnHero = $playerResults->where('hero', $mostPlayedHero)->count();
            $winsOnMostPlayedHero = $playerResults->where('hero', $mostPlayedHero)->where('winner', 1)->count();

            $winRateOnMostPlayedHero = ($totalGamesOnHero > 0) ? round(($winsOnMostPlayedHero / $totalGamesOnHero) * 100, 2) : 0;

            $mostPlayedRole = $playerResults->groupBy('new_role')->sortDesc()->keys()->first();
            $totalGames = $playerResults->count();
            $blizzId = $playerResults->first()->blizz_id;

            $battletag = explode('#', $battletag)[0];

            if ($this->esport == 'hi' || $this->esport == 'hi_nc') {
                $playerlink = "/Esports/HeroesInternational/Player/{$battletag}/{$blizzId}";
            } else {
                $playerlink = "/Esports/{$this->esport}/Player/{$battletag}/{$blizzId}";
            }

            if ($this->season) {
                $playerlink .= "?season={$this->season}";

                if ($this->division) {
                    $playerlink .= "&division={$this->division}";
                }

                if ($this->tournament) {
                    $playerlink .= "&tournament={$this->tournament}";
                }

            } elseif ($this->division) {
                $playerlink .= "?division={$this->division}";

                if ($this->tournament) {
                    $playerlink .= "&tournament={$this->tournament}";
                }

            } elseif ($this->tournament) {
                $playerlink .= "?tournament={$this->tournament}";
            }

            if ($this->esport == 'hi' || $this->esport == 'hi_nc') {
                $herolink = "/Esports/HeroesInternational/Player/{$battletag}/{$blizzId}/Hero/{$heroData[$mostPlayedHero]['name']}";
            }else if($this->esport == 'Other'){
                $herolink = "/Esports/{$this->esport}/" . $this->series . "/Player/{$battletag}/{$blizzId}/Hero/{$heroData[$mostPlayedHero]['name']}";
            } else {
                $herolink = "/Esports/{$this->esport}/Player/{$battletag}/{$blizzId}/Hero/{$heroData[$mostPlayedHero]['name']}";
            }

            if ($this->season) {
                $herolink .= "?season={$this->season}";

                if ($this->division) {
                    $herolink .= "&division={$this->division}";
                }

                if ($this->tournament) {
                    $herolink .= "&tournament={$this->tournament}";
                }
            } elseif ($this->division) {
                $herolink .= "?division={$this->division}";
            } elseif ($this->tournament) {
                $herolink .= "?tournament={$this->tournament}";
            }

            return [
                'battletag' => explode('#', $battletag)[0],
                'blizz_id' => $blizzId,
                'most_played_hero' => $heroData[$mostPlayedHero],
                'win_rate_on_hero' => $winRateOnMostPlayedHero,
                'most_played_role' => $mostPlayedRole,
                'games_played' => $totalGames,
                'playerlink' => $playerlink,
                'herolink' => $herolink,
            ];
        })->sortByDesc('totalGames');

        $matches = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps) {

            if (! is_null($this->blizz_id)) {
                $heroes = [
                    0 => $group[0] && $group[0]->hero ? ['hero' => $heroData[$group[0]->hero]] : null,
                ];
            } else {
                $heroes = [];
                for ($i = 0; $i < 5; $i++) {
                    $heroes[$i] = isset($group[$i]) && $group[$i]->hero ? ['hero' => $heroData[$group[$i]->hero]] : null;
                }
            }

            $team_0_name = null;
            $team_1_name = null;

            if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                $team_0_name = $group[0]->team_0_name;
                $team_1_name = $group[0]->team_1_name;
            } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc') {
                $team_0_name = $group[0]->team_0_id;
                $team_1_name = $group[0]->team_1_id;
            }

            return [
                'replayID' => $group[0]->replayID,
                'game_date' => $group[0]->game_date,
                'game_map' => $maps[$group[0]->game_map],
                'game' => $group[0]->game,
                'round' => $group[0]->round,
                'team_0_name' => $team_0_name,
                'team_1_name' => $team_1_name,
                'winner' => 1,
                'heroes' => $heroes,
            ];
        })->sortByDesc('game_date')->take(10)->values()->all();

        $heroes = $results->groupBy('hero')->map(function ($group) use ($heroData) {
            $wins = $group->sum(function ($item) {
                return $item->winner == 1 ? 1 : 0;
            });

            $losses = $group->sum(function ($item) {
                return $item->winner == 0 ? 1 : 0;
            });

            $gamesPlayed = $wins + $losses;

            if ($this->esport == 'hi' || $this->esport == 'hi_nc') {
                $link = "/Esports/HeroesInternational/Player/{$this->battletag}/{$this->blizz_id}/Hero/{$heroData[$group[0]->hero]['name']}";

            } else {
                $link = "/Esports/{$this->esport}/Player/{$this->battletag}/{$this->blizz_id}/Hero/{$heroData[$group[0]->hero]['name']}";
            }

            if ($this->season) {
                $link .= "?season={$this->season}";

                if ($this->division) {
                    $link .= "&division={$this->division}";
                }
                if ($this->tournament) {
                    $link .= "&tournament={$this->tournament}";
                }
            } elseif ($this->division) {
                $link .= "?division={$this->division}";
                if ($this->tournament) {
                    $link .= "&tournament={$this->tournament}";
                }
            } elseif ($this->tournament) {
                $link .= "?tournament={$this->tournament}";
            }

            return [
                'hero_id' => $group[0]->hero,
                'hero' => $heroData[$group[0]->hero],
                'name' => $heroData[$group[0]->hero]['name'],
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'games_played' => $gamesPlayed,

                'battletag' => $this->battletag,
                'blizz_id' => $this->blizz_id,
                'season' => $this->season,
                'division' => $this->division,
                'link' => $link,
            ];
        });

        $filteredHeroes = $heroes->filter(function ($hero) {
            return is_null($this->blizz_id) ? $hero['games_played'] >= 5 : $hero['games_played'];
        });

        $heroTopthreeHighestWinRate = $filteredHeroes->sortByDesc('win_rate')->take(3);
        $heroTopthreeLowestWinRate = $filteredHeroes->sortBy('win_rate')->take(3);
        $heroTopthreeMostPlayed = $filteredHeroes->sortByDesc('games_played')->take(3);

        $mapData = $results->groupBy('game_map')->map(function ($group) use ($maps) {
            $wins = $group->sum(function ($item) {
                return $item->winner == 1 ? 1 : 0;
            }) / 5;

            $losses = $group->sum(function ($item) {
                return $item->winner == 0 ? 1 : 0;
            }) / 5;

            if (! is_null($this->blizz_id)) {
                $wins *= 5;
                $losses *= 5;
            }

            $gamesPlayed = $wins + $losses;

            if ($this->esport == 'hi' || $this->esport == 'hi_nc') {
                $link = "/Esports/HeroesInternational/Player/{$this->battletag}/{$this->blizz_id}/Map/{$maps[$group[0]->game_map]['name']}";
            } else {
                $link = "/Esports/{$this->esport}/Player/{$this->battletag}/{$this->blizz_id}/Map/{$maps[$group[0]->game_map]['name']}";
            }

            if ($this->season) {
                $link .= "?season={$this->season}";

                if ($this->division) {
                    $link .= "&division={$this->division}";
                }
                if ($this->tournament) {
                    $link .= "&tournament={$this->tournament}";
                }
            } elseif ($this->division) {
                $link .= "?division={$this->division}";
                if ($this->tournament) {
                    $link .= "&tournament={$this->tournament}";
                }
            } elseif ($this->tournament) {
                $link .= "?tournament={$this->tournament}";
            }

            return [
                'map_id' => $group[0]->game_map,
                'map' => $maps[$group[0]->game_map],
                'game_map' => $maps[$group[0]->game_map],
                'name' => $maps[$group[0]->game_map]['name'],
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'games_played' => $gamesPlayed,

                'battletag' => $this->battletag,
                'blizz_id' => $this->blizz_id,
                'season' => $this->season,
                'division' => $this->division,
                'link' => $link,
            ];
        });

        $mapTopthreeHighestWinRate = $mapData->sortByDesc('win_rate')->take(3);
        $mapTopthreeLowestWinRate = $mapData->sortBy('win_rate')->take(3);
        $mapTopthreeMostPlayed = $mapData->sortByDesc('games_played')->take(3);

        $replayIDs = $results->pluck('replayID')->toArray();

        $mapBanData = null;
        $mapBanDataTransformed = [];

        if($this->esport != "Other"){
            $mapBanData = DB::table($this->schema.'.replay')
            ->selectRaw('distinct(round), team_0_map_ban as ban1, team_0_map_ban_2 as ban2')
            ->whereIn($this->schema.'.replay.replayID', $replayIDs)
            ->when($this->esport == 'NGS' || $this->esport == 'MastersClash', function ($query) {
                return $query->where('team_0_name', $this->team);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc', function ($query) {
                return $query->where('team_0_id', $this->team_name);
            })
            ->union(function ($query) use ($replayIDs) {
                $query->selectRaw('distinct(round), team_1_map_ban as ban1, team_1_map_ban_2 as ban2')
                    ->from($this->schema.'.replay')
                    ->whereIn($this->schema.'.replay.replayID', $replayIDs)
                    ->when($this->esport == 'NGS' || $this->esport == 'MastersClash', function ($query) {
                        return $query->where('team_1_name', $this->team);
                    })
                    ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc', function ($query) {
                        return $query->where('team_1_id', $this->team_name);
                    });
            })
            ->get();


            foreach ($mapBanData as $data) {
                if (! array_key_exists($data->ban1, $mapBanDataTransformed)) {
                    $mapBanDataTransformed[$data->ban1] = 0;
                }
    
                if (! array_key_exists($data->ban2, $mapBanDataTransformed)) {
                    $mapBanDataTransformed[$data->ban2] = 0;
                }
    
                $mapBanDataTransformed[$data->ban1]++;
                $mapBanDataTransformed[$data->ban2]++;
            }
        }
 



        $mapBanReturn = [];
        $counter = 0;

        foreach ($mapBanDataTransformed as $mapValue => $value) {
            if ($mapValue == 0 || $mapValue == '') {
                continue;
            }
            $mapBanReturn[$counter]['game_map'] = $maps[$mapValue];
            $mapBanReturn[$counter]['value'] = $value;
            $mapBanReturn[$counter]['inputhover'] = 'Times Banned: '.$value;
            $counter++;
        }
        $mapBanReturn = collect($mapBanReturn)->sortByDesc('value')->values()->all();

        if($this->esport == 'NGS' || $this->esport == 'MastersClash'){
          $seasons = DB::table($this->schema.'.teams')
          ->join($this->schema.'.player', function ($join) {
            $join->on($this->schema.'.player.team_name', '=', $this->schema.'.teams.team_id');
          })
          ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
          ->select('season')
          ->when(! is_null($this->team), function ($query) {
              return $query->where($this->schema.'.teams.team_name', $this->team);
          })
          ->when(! is_null($this->blizz_id), function ($query) {
              return $query->where($this->schema.'.player.blizz_id', $this->blizz_id);
          })
          ->when(! is_null($this->hero), function ($query) {
              return $query->where($this->schema.'.player.hero', $this->hero);
          })
          ->distinct()
          ->orderByDesc('season') 
          ->pluck('season')
          ->map(function ($season) {
              return ['code' => strval($season), 'name' => strval($season)];
          })
          ->values()
          ->all();
        }else if($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc' || $this->esport == 'Other'){
          $teamName = DB::table($this->schema.'.teams')->where("team_id", $this->team)->value("team_name");
          $seasons = DB::table($this->schema.'.teams')
          ->join($this->schema.'.player', function ($join) {
            $join->on($this->schema.'.player.team_id', '=', $this->schema.'.teams.team_id'); 
          })
          ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
          ->select('season')
          ->when(! is_null($this->team), function ($query) use ($teamName){
              return $query->where($this->schema.'.teams.team_name', $teamName);
          })
          ->when(! is_null($this->blizz_id), function ($query) {
              return $query->where($this->schema.'.player.blizz_id', $this->blizz_id);
          })
          ->when(! is_null($this->hero), function ($query) {
              return $query->where($this->schema.'.player.hero', $this->hero);
          })
          ->distinct()
          ->orderByDesc('season') // Order by season in descending order
          ->pluck('season')
          ->map(function ($season) {
              return ['code' => strval($season), 'name' => strval($season)];
          })
          ->values()
          ->all();
        }
        
        array_unshift($seasons, ['code' => null, 'name' => 'All']);

        $divisions = null;
        if ($this->esport == 'NGS') {
            $divisions = DB::table($this->schema.'.teams')
                ->join($this->schema.'.player', $this->schema.'.player.team_name', '=', $this->schema.'.teams.team_id')
                ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
                ->select('division')
                ->when(! is_null($this->team), function ($query) {
                    return $query->where($this->schema.'.teams.team_name', $this->team);
                })
                ->when(! is_null($this->blizz_id), function ($query) {
                    return $query->where($this->schema.'.player.blizz_id', $this->blizz_id);
                })
                ->when(! is_null($this->hero), function ($query) {
                    return $query->where($this->schema.'.player.hero', $this->hero);
                })
                ->distinct()
                ->pluck('division')
                ->map(function ($division) {
                    return ['code' => $division, 'name' => $division];
                })
                ->values()
                ->all();

            array_unshift($divisions, ['code' => null, 'name' => 'All']);
        }

        $wins = 0;
        $losses = 0;

        if (! is_null($this->battletag)) {
            $wins = $results->where('winner', 1)->count();
            $losses = $results->where('winner', 0)->count();
        } else {
            $wins = $results->where('winner', 1)->count() / 5;
            $losses = $results->where('winner', 0)->count() / 5;
        }

        $gamesPlayed = $wins + $losses;

        $win_rate = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0;

        $totalDeaths = $results->sum('deaths');

        $kdr = $totalDeaths > 0 ? round($results->sum('kills') / $totalDeaths, 2) : $results->sum('kills');
        $kda = $totalDeaths > 0 ? round($results->sum('takedowns') / $totalDeaths, 2) : $results->sum('takedowns');

        $totalSeconds = $results->sum('time_spent_dead');
        $days = floor($totalSeconds / (3600 * 24));
        $hours = floor(($totalSeconds % (3600 * 24)) / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $time_spent_dead = $days > 0 ? "{$days} days, {$hours} hours, {$minutes} minutes, {$seconds} seconds" : "{$hours} hours, {$minutes} minutes, {$seconds} seconds";

        $banned_data = $this->getBanData($results, 0, 1, $heroData);
        $enemy_banned_data = $this->getBanData($results, 1, 0, $heroData);

        $image = optional($results->first())->image;
        if ($this->esport == 'NGS') {
            if (strpos($image, 'https://s3.amazonaws.com/ngs-image-storage/') !== false) {
                $image = explode('https://s3.amazonaws.com/ngs-image-storage/', $image);
                $image = 'https://s3.amazonaws.com/ngs-image-storage/'.urlencode($image[1]);

                if (strpos($image, 'undefined') !== false) {
                    $image = '/images/NGS/no-image-clipped.png';
                }
            } else {
                $image = $image;
            }
        } elseif ($this->esport == 'CCL') {
            $image = '/images/CCL/Organizations/Logos/'.$image;
        } elseif ($this->esport == 'MastersClash') {
            $image = '/images/MCL/'.$image;
        } elseif ($this->esport == 'hi') {
            $image = '/images/HI/Team/Logos/'.$image.'.png';
        } elseif ($this->esport == 'hi_nc') {
            $image = '/images/HI/Flags/'.$image.'.png';
        }elseif ($this->esport == 'Other') {
            $image = '/images/EsportOther/' . Series::select("icon")->where("name", $this->series)->first()->icon;
        }

        return [
            'wins' => $wins,
            'losses' => $losses,
            'win_rate' => $win_rate,
            'kdr' => $kdr,
            'kda' => $kda,
            'takedowns' => $results->sum('takedowns'),
            'kills' => $results->sum('kills'),
            'assists' => $results->sum('assists'),
            'icon_url' => $image,
            'time_spent_dead' => $time_spent_dead,
            'deaths' => $totalDeaths,
            'total_games' => $gamesPlayed,
            'matches' => $matches,
            'heroes' => $heroes->sortBy('name')->values(),
            'hero_top_three_most_played' => $heroTopthreeMostPlayed->values(),
            'hero_top_three_highest_win_rate' => $heroTopthreeHighestWinRate->values(),
            'hero_top_three_lowest_win_rate' => $heroTopthreeLowestWinRate->values(),
            'maps' => $mapData->sortBy('name')->values(),
            'map_top_three_most_played' => $mapTopthreeMostPlayed->values(),
            'map_top_three_highest_win_rate' => $mapTopthreeHighestWinRate->values(),
            'map_top_three_lowest_win_rate' => $mapTopthreeLowestWinRate->values(),
            'seasons' => $seasons,
            'divisions' => $divisions,
            'players' => $players,
            'heroes_lost_against' => $topEnemyHeroes,
            'heroes_won_against' => $topAllyHeroes,
            'enemy_teams' => $topEnemyTeams,
            'team_ban_date' => $banned_data,
            'enemy_ban_date' => $enemy_banned_data,
            'maps_banned' => $mapBanReturn,
        ];
    }

    private function getTopEnemyAlly($replayIDs, $team, $heroData, $type)
    {
        $results = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', function ($join) {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_name');
                } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc' || $this->esport == 'Other') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
                }
            })
            ->select([
                $this->schema.'.replay.replayID',
                $this->schema.'.player.hero',
            ])
            ->whereIn($this->schema.'.replay.replayID', $replayIDs)
            ->whereNot($this->schema.'.teams.team_name', $team)
            ->get();
        $groupedResults = $results->groupBy('hero')->map(function ($group) {
            return $group->count();
        })->sortByDesc(function ($count) {
            return $count;
        });
        $returnData = [];
        $counter = 0;
        foreach ($groupedResults as $hero => $count) {
            $returnData[$counter]['hero'] = $heroData[$hero];
            $returnData[$counter]['total'] = $count;

            if ($type == 1) {
                $returnData[$counter]['inputhover'] = 'Won while on a team with '.$heroData[$hero]['name'].' '.$count.' times ('.round(($count / (count($replayIDs)) * 5) * 100, 2).'% of all games won as '.$team.')';
            } else {
                $returnData[$counter]['inputhover'] = 'Lost against a team with '.$heroData[$hero]['name'].' '.$count.' times ('.round(($count / (count($replayIDs)) * 5) * 100, 2).'% of all games lost as '.$team.')';
            }
            $counter++;
        }

        return $returnData;
    }

    private function getTopEnemyTeams($replayIDs, $team, $teamImageMap, $series)
    {
        $results = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', function ($join) {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_name');
                } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc' || $this->esport == 'Other') {
                    $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
                }
            })
            ->select([
                $this->schema.'.replay.replayID',
                $this->schema.'.teams.team_name',
                $this->schema.'.teams.image',
            ])
            ->whereIn($this->schema.'.replay.replayID', $replayIDs)
            ->when($this->esport == 'NGS' || $this->esport == 'MastersClash', function ($query) use ($team) {
                return $query->whereNot($this->schema.'.teams.team_name', $team);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc' || $this->esport == 'Other', function ($query) use ($team) {
                return $query->whereNot($this->schema.'.teams.team_id', $team);
            })
            ->get();

        $groupedResults = $results->groupBy('team_name')->map(function ($group) {
            return $group->count() / 5;
        })->sortByDesc(function ($count) {
            return $count;
        });

        $returnData = [];
        $counter = 0;

        foreach ($groupedResults as $enemyteam => $count) {
            $returnData[$counter]['team'] = $enemyteam;
            $returnData[$counter]['total'] = $count;

            $image = $teamImageMap[$enemyteam];

            if ($this->esport == 'NGS') {
                if (strpos($image, 'https://s3.amazonaws.com/ngs-image-storage/') !== false) {
                    $image = explode('https://s3.amazonaws.com/ngs-image-storage/', $image);
                    $image = 'https://s3.amazonaws.com/ngs-image-storage/'.urlencode($image[1]);

                    if (strpos($image, 'undefined') !== false) {
                        $image = '/images/NGS/no-image-clipped.png';
                    }
                } else {
                    $image = $image;
                }
            } elseif ($this->esport == 'CCL') {
                $image = '/images/CCL/Organizations/Logos/'.$image;
            } elseif ($this->esport == 'MastersClash') {
                $image = '/images/MCL/'.$image;
            } elseif ($this->esport == 'hi') {
                $image = '/images/HI/Team/Logos/'.$image.'.png';
            } elseif ($this->esport == 'hi_nc') {
                $image = '/images/HI/Flags/'.$image.'.png';
            }elseif ($this->esport == 'Other') {
                $image = '/images/EsportOther/' . Series::select("icon")->where("name", $series)->first()->icon;
            }

            $returnData[$counter]['icon_url'] = $image;

            $returnData[$counter]['inputhover'] = 'Lost against team '.$enemyteam.' '.$count.' times ('.round((($count / (count($replayIDs))) * 100) * 5, 2).'% of all games lost as '.$team.')';

            if ($this->esport == 'hi' || $this->esport == 'hi_nc') {
                $enemy_link = "/Esports/HeroesInternational/Team/{$enemyteam}";
            } else {
                $enemy_link = "/Esports/{$this->esport}/Team/{$enemyteam}";
            }

            if ($this->season) {
                $enemy_link .= "?season={$this->season}";

                if ($this->division) {
                    $enemy_link .= "&division={$this->division}";
                }
                if ($this->tournament) {
                    $enemy_link .= "&tournament={$this->tournament}";
                }
            } elseif ($this->division) {
                $enemy_link .= "?division={$this->division}";
                if ($this->tournament) {
                    $enemy_link .= "&tournament={$this->tournament}";
                }
            } elseif ($this->tournament) {
                $enemy_link .= "?tournament={$this->tournament}";
            }

            $returnData[$counter]['enemy_link'] = $enemy_link;

            $counter++;
            if ($counter == 5) {
                break;
            }
        }

        return $returnData;
    }

    private function getBanData($results, $team1, $team2, $heroData)
    {
        $replaysTeam0 = $results->filter(function ($result) {
            return $result->team === 0;
        });

        $replaysTeam1 = $results->filter(function ($result) {
            return $result->team === 1;
        });

        $banData0 = DB::table($this->schema.'.replay_bans')
            ->whereIn($this->schema.'.replay_bans.replayID', $replaysTeam0->pluck('replayID')->toArray())
            ->where($this->schema.'.replay_bans.team', $team1)
            ->get();

        $banData1 = DB::table($this->schema.'.replay_bans')
            ->whereIn($this->schema.'.replay_bans.replayID', $replaysTeam1->pluck('replayID')->toArray())
            ->where($this->schema.'.replay_bans.team', $team2)
            ->get();

        $banned_heroes = [];
        foreach ($banData0 as $banData) {

            if (! array_key_exists($banData->hero, $banned_heroes)) {
                $banned_heroes[$banData->hero] = 0;
            }
            $banned_heroes[$banData->hero]++;
        }
        foreach ($banData1 as $banData) {

            if (! array_key_exists($banData->hero, $banned_heroes)) {
                $banned_heroes[$banData->hero] = 0;
            }
            $banned_heroes[$banData->hero]++;
        }

        $return_banned_data = [];
        $counter = 0;
        foreach ($banned_heroes as $heroBanData => $value) {
            $return_banned_data[$counter]['hero'] = $heroData[$heroBanData];
            $return_banned_data[$counter]['bans'] = $value;
            $return_banned_data[$counter]['inputhover'] = 'Times Banned: '.$value;
            $counter++;
        }

        usort($return_banned_data, function ($a, $b) {
            return $b['bans'] - $a['bans'];
        });

        return $return_banned_data;
    }
}
