<?php

namespace App\Http\Controllers\Esports;

use App\Http\Controllers\Controller;
use App\Models\CCL\CCLTeam;
use App\Models\Esports\Other\Series;
use App\Models\HeroesDataTalent;
use App\Models\HeroesInternational\HeroesInternationalMainTeam;
use App\Models\HeroesInternational\HeroesInternationalNationsCupTeam;
use App\Models\Map;
use App\Models\NGS\NGSTeam;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\NGSDivisionInputValidation;
use App\Rules\RoleInputValidation;
use App\Support\GameLength;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    /** Every league's player match history, Other's too (EsportOtherController hands it here). */
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
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id'), $validationRules);
        $otherValidator = Validator::make($request->all(), $otherValidationRules);

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

        return view('Esports.singlePlayerMatchHistory')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'esport' => $esport,
            'filters' => $this->globalDataService->getFilterData(),
            'season' => $request['season'],
            'tournament' => $request['tournament'],
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
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make(compact('esport', 'team'), $validationRules);

        $otherValidator = Validator::make($request->all(), $otherValidationRules);

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
                'filters' => $this->globalDataService->getFilterData(),
                'esport' => $esport,
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
            'team' => 'nullable|string',
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id'), $validationRules);

        $otherValidator = Validator::make($request->all(), $otherValidationRules);

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

        return view('Esports.singlePlayer')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
                'esport' => $esport,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => $request['division'],
                'team' => $request['team'],
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
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id', 'hero'), $validationRules);

        $otherValidator = Validator::make($request->all(), $otherValidationRules);

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

        $hero = $this->globalDataService->getHeroModel($request['hero']);

        return view('Esports.singlePlayerHero')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
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
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make(compact('esport', 'battletag', 'blizz_id', 'game_map'), $validationRules);

        $otherValidator = Validator::make($request->all(), $otherValidationRules);

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
        $mapobject = Map::where('name', $request['game_map'])->first();

        return view('Esports.singlePlayerMap')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
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
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make(compact('esport', 'team'), $validationRules);
        $otherValidator = Validator::make($request->all(), $otherValidationRules);

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
        // return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'team' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'pagination_page' => 'required:integer',
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
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
        $tournament = $request['tournament'];

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport == 'HeroesInternational') {
            if ($tournament == 'main') {
                $this->schema .= '_hi';
            } elseif ($tournament == 'nationscup') {
                $this->schema .= '_hi_nc';
            }
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

        // return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'team' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'pagination_page' => 'required:integer',
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
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
        // return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational,hi,hi_nc',
            'team' => 'nullable|string',
            'team_name' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'game_map' => ['sometimes', 'nullable',  new GameMapInputValidation],
            'pagination_page' => 'required:integer',
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => [$request->all()],
                'status' => 'failure to validate inputs',
            ];
        }

        $this->esport = $request['esport'];
        if ($this->esport == 'HeroesInternational') {
            $this->esport = $request['tournament'] == 'nationscup' ? 'hi_nc' : 'hi';
        }
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

        // Which league's logo folder team images come from; HI splits main and Nations Cup
        $logoLeague = $this->esport;

        // A team page's games; CCL and HI keep the team name in replay.team_*_id
        $teamName = in_array($this->esport, ['CCL', 'hi', 'hi_nc']) ? $request['team_name'] : null;

        $pagination_page = $request['pagination_page'];
        $perPage = 1000;

        if ($this->hero) {
            $perPage = 5000;

        }
        $results = DB::table($this->schema.'.replay')->select($this->schema.'.replay.replayID', 'hero', 'game_map', 'round', 'game', 'game_date', $this->schema.'.player.team', $this->schema.'.player.winner', $this->schema.'.player.blizz_id', $this->schema.'.battletags.battletag AS battletag_name', $this->schema.'.teams.image AS team_image', $this->schema.'.teams.team_name AS side_team_name')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->leftJoin($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
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
            ->when(! is_null($this->season), function ($query) {
                return $query->where('replay.season', $this->season);
            })
            ->when(! is_null($this->division), function ($query) {
                return $query->where($this->schema.'.teams.division', $this->division);
            })
            ->when(in_array($this->esport, ['NGS', 'MastersClash']) && ! is_null($this->team), function ($query) {
                return $query->where(function ($query) {
                    $query->where($this->schema.'.replay.team_0_name', $this->team)
                        ->orWhere($this->schema.'.replay.team_1_name', $this->team);
                });
            })
            ->when(! is_null($teamName), function ($query) use ($teamName) {
                return $query->where(function ($query) use ($teamName) {
                    $query->where($this->schema.'.replay.team_0_id', $teamName)
                        ->orWhere($this->schema.'.replay.team_1_id', $teamName);
                });
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

        $groupedResults = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps, $logoLeague) {
            $heroes = [];

            $foundHero = false;

            for ($i = 0; $i < 10; $i++) {
                $heroes[$i] = isset($group[$i]) && isset($group[$i]->hero) ? $heroData[$group[$i]->hero] : null;

                if ($this->hero && isset($group[$i]) && isset($group[$i]->hero) && $heroes[$i]['id'] == $this->hero) {
                    $foundHero = true;
                }

            }

            if ($this->hero && ! $foundHero) {
                return null;
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

            if ($this->esport == 'CCL' && $this->team && ($group[0]->team_0_id != $this->team && $group[0]->team_1_id != $this->team)) {
                return null;
            }

            // Each side's players with their heroes, and which side won, for the expanded match rows
            // Names, logos, players and the winner all keyed by the players' own side (player.team),
            // as replay.team_0_name doesn't always sit on side 0
            $teams = [[], []];
            $teamImages = [null, null];
            $sideNames = [null, null];
            $winner = null;
            foreach ($group as $row) {
                if (array_key_exists($row->team, $sideNames) && $sideNames[$row->team] === null && $row->side_team_name) {
                    $sideNames[$row->team] = $row->side_team_name;
                }
                if (array_key_exists($row->team, $teamImages) && $teamImages[$row->team] === null && $row->team_image) {
                    $teamImages[$row->team] = $this->teamImageUrl($row->team_image, $logoLeague);
                }
                if (isset($row->hero, $heroData[$row->hero]) && isset($teams[$row->team])) {
                    $teams[$row->team][] = [
                        'hero' => $heroData[$row->hero],
                        'battletag' => $row->battletag_name ? explode('#', $row->battletag_name)[0] : null,
                        'blizz_id' => $row->blizz_id,
                    ];
                }
                if ($row->winner == 1) {
                    $winner = (int) $row->team;
                }
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
                'teams' => $teams,
                'team_images' => $teamImages,
                'side_names' => $sideNames,
                'winner' => $winner,
            ];
        })->filter()->values()->all();

        $response = ['data' => $groupedResults, 'pagination' => $paginationInfo];

        // The division's teams that season, for the division match history's Teams filter
        if ($this->esport == 'NGS' && ! is_null($this->division)) {
            $response['teams'] = DB::table($this->schema.'.teams')
                ->where('season', $this->season)
                ->where('division', $this->division)
                ->distinct()
                ->orderBy('team_name')
                ->pluck('team_name')
                ->all();
        }

        return $response;
    }

    /**
     * The esports player page's profile-style panels, all from this league's own games:
     * the teams they played for, win rate by role, weekday win rates (UTC hour-of-week
     * buckets, as the profile page sends them) and total time played.
     */
    private function playerProfileExtras($results): array
    {
        $teams = $results->groupBy(fn ($row) => $row->team_label.'|'.($row->team_season ?? ''))
            ->map(function ($games) {
                $first = $games->first();
                $wins = $games->where('winner', 1)->count();
                $link = $this->leagueUrl().'/Team/'.rawurlencode($first->team_label);
                $query = array_filter(['season' => $first->team_season ?? null, 'division' => $first->team_division ?? null, 'tournament' => $this->tournament]);

                return [
                    'team_name' => $first->team_label,
                    'image' => $first->image ? $this->teamImageUrl($first->image, $this->esport) : null,
                    'division' => $first->team_division ?? null,
                    'season' => $first->team_season ?? null,
                    'games_played' => $games->count(),
                    'win_rate' => round($wins / $games->count() * 100, 2),
                    'link' => $query ? $link.'?'.http_build_query($query) : $link,
                ];
            })
            ->sortByDesc('season')
            ->values();

        $roles = $results->groupBy('new_role')
            ->map(fn ($games, $role) => [
                'role' => $role,
                'games_played' => $games->count(),
                'win_rate' => round($games->where('winner', 1)->count() / $games->count() * 100, 2),
            ])
            ->sortBy('role')
            ->values();

        $weekdays = [];
        foreach ($results as $row) {
            $date = Carbon::parse($row->game_date);
            $bucket = ($date->dayOfWeekIso - 1) * 24 + $date->hour;
            $weekdays[$bucket] ??= ['wins' => 0, 'losses' => 0];
            $weekdays[$bucket][$row->winner == 1 ? 'wins' : 'losses']++;
        }

        $seconds = (int) $results->sum(fn ($row) => GameLength::played($row->game_length, null));

        return [
            'teams_played_for' => $teams,
            'role_win_rates' => $roles,
            'weekday_data' => $weekdays,
            'total_time_played' => sprintf('%dd, %dh, %dm, %ds', intdiv($seconds, 86400), intdiv($seconds % 86400, 3600), intdiv($seconds % 3600, 60), $seconds % 60),
        ];
    }

    /**
     * Both teams' players for a player's recent games, shaped like the match history's
     * players column: their own team first as one party, the other team as another.
     */
    private function withMatchPlayers(array $matches, $heroData): array
    {
        $players = DB::table($this->schema.'.player')
            ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
            ->select([
                $this->schema.'.player.replayID',
                $this->schema.'.player.hero',
                $this->schema.'.player.team',
                $this->schema.'.player.blizz_id',
                $this->schema.'.player.hero_level',
                $this->schema.'.player.mastery_tier',
                $this->schema.'.battletags.battletag',
            ])
            ->whereIn($this->schema.'.player.replayID', array_column($matches, 'replayID'))
            ->get()
            ->groupBy('replayID');

        foreach ($matches as &$match) {
            $sides = [[], []];
            foreach ($players[$match['replayID']] ?? [] as $player) {
                $own = $player->team == $match['own_side'];
                $name = explode('#', $player->battletag)[0];
                $sides[$own ? 0 : 1][] = [
                    'hero' => $heroData[$player->hero] ?? null,
                    'battletag' => $name,
                    'blizz_id' => $player->blizz_id,
                    'party' => $own ? 'blue' : 'red',
                    // The profile's popup, from what the leagues keep: no account level, and no MMR
                    'hero_level' => $this->globalDataService->heroLevelLabel($player->hero_level, $player->mastery_tier),
                    'own' => $player->blizz_id == $this->blizz_id,
                    'link' => $this->withLeagueQuery($this->playerPath($name, $player->blizz_id)),
                ];
            }
            // The page's own player first in their team
            usort($sides[0], fn ($a, $b) => $b['own'] <=> $a['own']);
            $match['players'] = $sides;
        }
        unset($match);

        return $matches;
    }

    /** A team's stored image as a URL, the way the team page builds it for each league. */
    private function teamImageUrl($image, $league)
    {
        if ($league == 'NGS') {
            if (strpos($image, 'https://s3.amazonaws.com/ngs-image-storage/') !== false) {
                $image = 'https://s3.amazonaws.com/ngs-image-storage/'.urlencode(explode('https://s3.amazonaws.com/ngs-image-storage/', $image)[1]);

                if (strpos($image, 'undefined') !== false) {
                    $image = '/images/NGS/no-image-clipped.png';
                }
            }

            return $image;
        }

        return match ($league) {
            'CCL' => '/images/CCL/Organizations/Logos/'.$image,
            'MastersClash' => '/images/MCL/'.$image,
            'hi' => '/images/HI/Team/Logos/'.$image.'.png',
            'hi_nc' => '/images/HI/Flags/'.$image.'.png',
            // Other's pages show the series icon for every team
            'Other' => '/images/EsportOther/'.($this->seriesIcon ??= Series::where('name', $this->series)->value('icon') ?? 'heroesprofilelogo.png'),
            default => $image,
        };
    }

    public function getOverallHeroStats(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,NutCup,hi,hi_nc',
            'season' => 'required|numeric',
            'division' => ['sometimes', 'nullable', new NGSDivisionInputValidation],
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
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
        // return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,NutCup,hi,hi_nc',
            'season' => 'required|numeric',
            'division' => ['sometimes', 'nullable', new NGSDivisionInputValidation],
            'hero' => ['required', new HeroInputValidation],
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
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
            // ->toSql();
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
        // return response()->json($request->all());

        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational,Other',
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
            'series' => 'required_if:esport,Other|nullable|string',
            'team' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'division' => 'nullable|string',
            'season' => 'nullable|numeric',
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'game_map' => ['sometimes', 'nullable',  new GameMapInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'tournament' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('esport') === 'HeroesInternational'),
                'in:main,nationscup',
            ],
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
        $this->series = $this->esport === 'Other' ? $request['series'] : null;

        $this->hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;
        $this->game_map = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id')->toArray() : null;

        if ($this->esport == 'CCL') {
            $this->team = $request['team'] ? CCLTeam::when(! is_null($this->season), function ($query) {
                return $query->where('season', $this->season);
            })->where('team_name', $request['team'])->value('team_id') ?? -1
            : null;

            $this->team_name = $request['team'];
        } elseif ($this->esport == 'hi' && $this->tournament == 'main') {
            $this->team = $request['team'] ? HeroesInternationalMainTeam::where('season', 1)->where('team_name', $request['team'])->value('team_id') ?? -1 : null;
            $this->team_name = $request['team'];
        } elseif ($this->esport == 'hi_nc' && $this->tournament == 'nationscup') {

            $this->team = $request['team'] ? HeroesInternationalNationsCupTeam::where('season', 1)->where('team_name', $request['team'])->value('team_id') ?? -1 : null;
            $this->team_name = $request['team'];
        }

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport == 'HeroesInternational' && $this->tournament == 'main') {
            $this->schema .= '_hi';
        } elseif ($this->esport == 'Other') {
            $this->schema .= '_ml';
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
            ->join($this->schema.'.teams', $this->schema.'.teams.team_id', '=', $this->schema.'.player.'.$this->league()['player_team'])
            ->join('heroes', 'heroes.id', '=', $this->schema.'.player.hero')
            ->select([
                $this->schema.'.teams.team_name',
                // Under its own name too: the NGS addSelect below reuses team_name for the team ID
                $this->schema.'.teams.team_name AS team_label',
                $this->schema.'.teams.image',
                $this->schema.'.replay.game_length',
                $this->schema.'.battletags.battletag',
                $this->schema.'.battletags.blizz_id',
                $this->schema.'.replay.replayID',
                $this->schema.'.replay.game_date',
                $this->schema.'.replay.game_map',
                $this->schema.'.replay.game',
                $this->schema.'.replay.round',
                // heroesprofile_ml has no map bans
                ...($this->esport == 'Other' ? [] : [
                    $this->schema.'.replay.team_0_map_ban',
                    $this->schema.'.replay.team_0_map_ban_2',
                    $this->schema.'.replay.team_1_map_ban',
                    $this->schema.'.replay.team_1_map_ban_2',
                ]),
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
                ]);
            })
            ->when($this->esport == 'NGS', function ($query) {
                return $query->addSelect([
                    $this->schema.'.teams.division AS team_division',
                    $this->schema.'.teams.season AS team_season',
                ]);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_id',
                    $this->schema.'.replay.team_1_id',
                    $this->schema.'.player.team_id',
                ]);
            })
            ->when($this->esport == 'Other', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                    $this->schema.'.player.team_id',
                ])->where($this->schema.'.replay.series', $this->series);
            })
            ->when(! is_null($this->game_map), function ($query) {
                return $query->where($this->schema.'.replay.game_map', $this->game_map);
            })
            ->when(! is_null($this->team) && in_array($this->esport, ['NGS', 'MastersClash', 'Other']), function ($query) {
                return $query->where($this->schema.'.teams.team_name', $this->team);
            })
            ->when(! is_null($this->team) && ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc'), function ($query) {
                return $query->where($this->schema.'.teams.team_id', $this->team);
            })
            ->when(! is_null($this->blizz_id), function ($query) {
                return $query->where($this->schema.'.player.blizz_id', $this->blizz_id);
            })
            ->when(! is_null($this->hero), function ($query) {
                return $query->where($this->schema.'.player.hero', $this->hero);
            })
            ->when(! is_null($request['role']), function ($query) use ($request) {
                return $query->where('heroes.new_role', $request['role']);
            })
            ->when(! is_null($this->division), function ($query) {
                return $query->where($this->schema.'.teams.division', $this->division);
            })
            ->when(! is_null($this->season), function ($query) {
                return $query->where($this->schema.($this->esport == 'Other' ? '.replay' : '.teams').'.season', $this->season);
            })
        // ->toSql();
            ->get();

        // return $results;

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

        // A player page has no team to leave out; it leaves out the player's own side in each game instead
        $playerSides = $this->blizz_id ? $results->pluck('team', 'replayID')->all() : null;

        $topEnemyHeroes = $this->getTopEnemyAlly($replaysLost->pluck('replayID')->toArray(), $this->team, $heroData, 0, $playerSides);
        $topAllyHeroes = $this->getTopEnemyAlly($replaysWon->pluck('replayID')->toArray(), $this->team, $heroData, 1, $playerSides);

        $teamData = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', $this->schema.'.teams.team_id', '=', $this->schema.'.player.'.$this->league()['player_team'])
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

        $topEnemyTeams = $this->getTopEnemyTeams($replaysLost->pluck('replayID')->toArray(), $this->team, $teamImageMap);

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

            $playerlink = $this->leagueUrl()."/Player/{$battletag}/{$blizzId}";

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

            $herolink = $this->leagueUrl()."/Player/{$battletag}/{$blizzId}/Hero/{$heroData[$mostPlayedHero]['name']}";

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

        $talentData = $this->blizz_id ? $this->globalDataService->getAllTalentsKeyed() : null;

        $matches = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps, $talentData) {

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

            if (in_array($this->esport, ['NGS', 'MastersClash', 'Other'])) {
                $team_0_name = $group[0]->team_0_name;
                $team_1_name = $group[0]->team_1_name;
            } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc') {
                $team_0_name = $group[0]->team_0_id;
                $team_1_name = $group[0]->team_1_id;
            }

            $match = [
                'replayID' => $group[0]->replayID,
                'game_date' => $group[0]->game_date,
                'game_map' => $maps[$group[0]->game_map],
                'game' => $group[0]->game,
                'round' => $group[0]->round,
                'team_0_name' => $team_0_name,
                'team_1_name' => $team_1_name,
                'winner' => $group[0]->winner,
                'heroes' => $heroes,
            ];

            // A player's own game, for the match history style rows: their hero, score line,
            // talents, the length, and their team against the other one
            if (! is_null($this->blizz_id)) {
                $row = $group[0];
                $match['hero'] = $heroData[$row->hero] ?? null;
                $match['kills'] = $row->kills;
                $match['deaths'] = $row->deaths;
                $match['assists'] = $row->assists;
                $match['game_length'] = GameLength::played($row->game_length, null);
                foreach (['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'] as $level) {
                    $match[$level] = $row->$level && $talentData->has($row->$level) ? $talentData[$row->$level] : null;
                }
                $match['own_team'] = $row->team_label;
                $match['opponent_team'] = $team_0_name === $row->team_label ? $team_1_name : $team_0_name;
                $match['own_side'] = $row->team;
            }

            return $match;
        })->sortByDesc('game_date')->take(10)->values()->all();

        if (! is_null($this->blizz_id)) {
            $matches = $this->withMatchPlayers($matches, $heroData);
        }

        $heroes = $results->groupBy('hero')->map(function ($group) use ($heroData) {
            $wins = $group->sum(function ($item) {
                return $item->winner == 1 ? 1 : 0;
            });

            $losses = $group->sum(function ($item) {
                return $item->winner == 0 ? 1 : 0;
            });

            $gamesPlayed = $wins + $losses;

            $link = $this->leagueUrl()."/Player/{$this->battletag}/{$this->blizz_id}/Hero/{$heroData[$group[0]->hero]['name']}";

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

            $link = $this->leagueUrl()."/Player/{$this->battletag}/{$this->blizz_id}/Map/{$maps[$group[0]->game_map]['name']}";

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

        $mapBanData = $this->esport == 'Other' ? collect() : DB::table($this->schema.'.replay')
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

        $mapBanDataTransformed = [];

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

        if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
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
        } elseif ($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc') {
            $teamName = DB::table($this->schema.'.teams')->where('team_id', $this->team)->value('team_name');
            $seasons = DB::table($this->schema.'.teams')
                ->join($this->schema.'.player', function ($join) {
                    $join->on($this->schema.'.player.team_id', '=', $this->schema.'.teams.team_id');
                })
                ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
                ->select('season')
                ->when(! is_null($this->team), function ($query) use ($teamName) {
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
        } elseif ($this->esport == 'Other') {
            $seasons = DB::table($this->schema.'.replay')
                ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
                ->join($this->schema.'.teams', $this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id')
                ->where($this->schema.'.replay.series', $this->series)
                ->whereNotNull($this->schema.'.replay.season')
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
                ->pluck($this->schema.'.replay.season')
                ->sortDesc()
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
        } elseif ($this->esport == 'Other') {
            $image = $this->teamImageUrl($image, 'Other');
        }

        return [
            ...($this->blizz_id ? $this->playerProfileExtras($results) : []),
            ...($this->blizz_id && $this->esport == 'CCL' ? ['ccl_mmr_data' => $this->cclMmrData()] : []),
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

    /** All-time CCL MMR, NA only, shaped like the main profile's *_mmr_data. */
    private function cclMmrData(): ?array
    {
        $row = DB::table('heroesprofile_ccl.master_mmr_data')
            ->where('blizz_id', $this->blizz_id)
            ->where('region', 1)
            ->first(['conservative_rating', 'win', 'loss']);

        if (! $row) {
            return null;
        }

        $games = $row->win + $row->loss;

        return [
            'win' => $row->win,
            'loss' => $row->loss,
            'win_rate' => $games > 0 ? round($row->win / $games * 100, 2) : 0,
            'mmr' => round(1800 + 40 * $row->conservative_rating),
        ];
    }

    private function getTopEnemyAlly($replayIDs, $team, $heroData, $type, $playerSides = null)
    {
        $results = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', $this->schema.'.teams.team_id', '=', $this->schema.'.player.'.$this->league()['player_team'])
            ->select([
                $this->schema.'.replay.replayID',
                $this->schema.'.player.hero',
                $this->schema.'.player.team',
            ])
            ->whereIn($this->schema.'.replay.replayID', $replayIDs)
            ->when(is_null($playerSides), function ($query) use ($team) {
                return $query->whereNot($this->schema.'.teams.team_name', $team);
            })
            ->get()
            ->when(! is_null($playerSides), function ($rows) use ($playerSides) {
                return $rows->filter(fn ($row) => isset($playerSides[$row->replayID]) && $row->team != $playerSides[$row->replayID]);
            });

        $asWho = is_null($playerSides) ? $team : $this->battletag;
        // A team's replay IDs repeat once per player (five per game); a player's appear once per game
        $rowsPerGame = is_null($playerSides) ? 5 : 1;
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
                $returnData[$counter]['inputhover'] = 'Won while on a team with '.$heroData[$hero]['name'].' '.$count.' times ('.round(($count / (count($replayIDs)) * $rowsPerGame) * 100, 2).'% of all games won as '.$asWho.')';
            } else {
                $returnData[$counter]['inputhover'] = 'Lost against a team with '.$heroData[$hero]['name'].' '.$count.' times ('.round(($count / (count($replayIDs)) * $rowsPerGame) * 100, 2).'% of all games lost as '.$asWho.')';
            }
            $counter++;
        }

        return $returnData;
    }

    private function getTopEnemyTeams($replayIDs, $team, $teamImageMap)
    {
        $results = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.teams', $this->schema.'.teams.team_id', '=', $this->schema.'.player.'.$this->league()['player_team'])
            ->select([
                $this->schema.'.replay.replayID',
                $this->schema.'.teams.team_name',
                $this->schema.'.teams.image',
            ])
            ->whereIn($this->schema.'.replay.replayID', $replayIDs)
            ->when(in_array($this->esport, ['NGS', 'MastersClash', 'Other']), function ($query) use ($team) {
                return $query->whereNot($this->schema.'.teams.team_name', $team);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'hi' || $this->esport == 'hi_nc', function ($query) use ($team) {
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
            } elseif ($this->esport == 'Other') {
                $image = $this->teamImageUrl($image, 'Other');
            }

            $returnData[$counter]['icon_url'] = $image;

            $returnData[$counter]['inputhover'] = 'Lost against team '.$enemyteam.' '.$count.' times ('.round((($count / (count($replayIDs))) * 100) * 5, 2).'% of all games lost as '.$team.')';

            $enemy_link = $this->leagueUrl()."/Team/{$enemyteam}";

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

    /*
     * Esports player pages built on the main profile's pages: Friends and Foes, Matchups,
     * Talents, Heroes, Maps and Roles, from one league's games only.
     */
    private const SCORE_COLUMNS = [
        'kills', 'assists', 'takedowns', 'deaths', 'highest_kill_streak', 'hero_damage', 'siege_damage',
        'structure_damage', 'minion_damage', 'creep_damage', 'summon_damage', 'time_cc_enemy_heroes',
        'healing', 'self_healing', 'damage_taken', 'experience_contribution', 'town_kills', 'time_spent_dead',
        'merc_camp_captures', 'watch_tower_captures', 'meta_experience', 'protection_allies',
        'silencing_enemies', 'rooting_enemies', 'stunning_enemies', 'clutch_heals', 'escapes', 'vengeance',
        'outnumbered_deaths', 'teamfight_escapes', 'teamfight_healing', 'teamfight_damage_taken',
        'teamfight_hero_damage', 'multikill', 'physical_damage', 'spell_damage', 'regen_globes', 'first_to_ten',
    ];

    /**
     * Each league with player pages, by its internal key (HI main is hi, Nations Cup hi_nc).
     * player_team: the player column teams.team_id joins on. replay_teams: whether replay
     * names its teams team_N_name or team_N_id. season: the table whose season column filters.
     */
    private const LEAGUES = [
        'NGS' => ['schema' => 'heroesprofile_ngs', 'player_team' => 'team_name', 'replay_teams' => 'name', 'party' => true, 'division' => true, 'season' => 'teams'],
        'MastersClash' => ['schema' => 'heroesprofile_mcl', 'player_team' => 'team_name', 'replay_teams' => 'name', 'party' => true, 'division' => false, 'season' => 'teams'],
        'CCL' => ['schema' => 'heroesprofile_ccl', 'player_team' => 'team_id', 'replay_teams' => 'id', 'party' => false, 'division' => false, 'season' => 'teams'],
        'hi' => ['schema' => 'heroesprofile_hi', 'player_team' => 'team_id', 'replay_teams' => 'id', 'party' => false, 'division' => false, 'season' => 'teams'],
        'hi_nc' => ['schema' => 'heroesprofile_hi_nc', 'player_team' => 'team_id', 'replay_teams' => 'id', 'party' => false, 'division' => false, 'season' => 'teams'],
        'Other' => ['schema' => 'heroesprofile_ml', 'player_team' => 'team_id', 'replay_teams' => 'name', 'party' => false, 'division' => false, 'season' => 'replay'],
    ];

    private $seriesIcon;

    private function league(): array
    {
        return self::LEAGUES[$this->esport];
    }

    /** The league's URL root: /Esports/NGS, /Esports/HeroesInternational, /Esports/Other/{series}. */
    private function leagueUrl(): string
    {
        return match ($this->esport) {
            'hi', 'hi_nc' => '/Esports/HeroesInternational',
            'Other' => '/Esports/Other/'.rawurlencode($this->series),
            default => '/Esports/'.$this->esport,
        };
    }

    /** HI pages need the tournament on every link. */
    private function withLeagueQuery(string $url): string
    {
        return in_array($this->esport, ['hi', 'hi_nc']) ? $url.'?tournament='.$this->tournament : $url;
    }

    private function seasonColumn(): string
    {
        return $this->schema.'.'.$this->league()['season'].'.season';
    }

    /**
     * Sets the league from the route ({esport}, or {series} for Other) and ?tournament= for HI.
     * False when it isn't a league with player pages.
     */
    private function setPlayerLeague(Request $request): bool
    {
        $series = $request->route('series');
        $esport = is_null($series) ? strtolower((string) ($request->route('esport') ?? 'ngs')) : 'other';

        $key = match ($esport) {
            'ngs' => 'NGS',
            'ccl' => 'CCL',
            'mastersclash' => 'MastersClash',
            'heroesinternational' => match ($request['tournament']) {
                'main' => 'hi',
                'nationscup' => 'hi_nc',
                default => null,
            },
            'other' => 'Other',
            default => null,
        };

        if (is_null($key)) {
            return false;
        }

        $this->esport = $key;
        $this->schema = self::LEAGUES[$key]['schema'];
        $this->tournament = in_array($key, ['hi', 'hi_nc']) ? $request['tournament'] : null;
        $this->series = $key === 'Other' ? $series : null;

        return true;
    }

    /** The esport/tournament/series a getData call needs for this league. */
    private function leagueDataInput(): array
    {
        return match ($this->esport) {
            'hi', 'hi_nc' => ['esport' => 'HeroesInternational', 'tournament' => $this->tournament],
            'Other' => ['esport' => 'Other', 'series' => $this->series],
            default => ['esport' => $this->esport],
        };
    }

    private function playerPath($battletag, $blizz_id): string
    {
        return $this->leagueUrl().'/Player/'.rawurlencode($battletag).'/'.$blizz_id;
    }

    public function showEsportPlayerFriendFoe(Request $request)
    {
        return $this->showEsportPlayerPage($request, 'Player.friendfoe');
    }

    public function showEsportPlayerMatchups(Request $request)
    {
        return $this->showEsportPlayerPage($request, 'Player.matchupData');
    }

    public function showEsportPlayerTalents(Request $request)
    {
        $hero = $request->route('hero');
        if (! is_null($hero) && ! (new HeroInputValidation)->passes('hero', $hero)) {
            return $this->redirectToPlayerPage($request, '/Talents');
        }

        return $this->showEsportPlayerPage($request, 'Player.talentData', [
            'userinput' => $this->globalDataService->getHeroModel($hero),
            'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            'heroes' => $this->globalDataService->getHeroes(),
        ]);
    }

    public function showEsportPlayerHeroes(Request $request)
    {
        return $this->showEsportPlayerPage($request, 'Player.Heroes.allHeroesData', [
            'account_level' => 0,
        ]);
    }

    public function showEsportPlayerHero(Request $request)
    {
        $hero = $request->route('hero');
        if (! (new HeroInputValidation)->passes('hero', $hero)) {
            return $this->redirectToPlayerPage($request, '/Hero');
        }

        return $this->showEsportPlayerPage($request, 'Player.Heroes.singleHeroData', [
            'hero' => $hero,
            'heroObject' => $this->globalDataService->getHeroModel($hero),
            'heroes' => $this->globalDataService->getHeroes(),
            'replayViewerAllowed' => false,
            'analyticsCutover' => config('analytics.cutover'),
            'replayDownloadBlocked' => false,
        ]);
    }

    public function showEsportPlayerMap(Request $request)
    {
        $map = $request->route('map');
        if (! (new GameMapInputValidation)->passes('game_map', $map)) {
            return $this->redirectToPlayerPage($request, '/Map');
        }

        return $this->showEsportPlayerPage($request, 'Player.Maps.singleMapData', [
            'map' => $map,
            'mapobject' => Map::where('name', $map)->first(),
            'replayViewerAllowed' => false,
            'analyticsCutover' => config('analytics.cutover'),
            'replayDownloadBlocked' => false,
        ]);
    }

    public function showEsportPlayerMaps(Request $request)
    {
        return $this->showEsportPlayerPage($request, 'Player.Maps.allMapData', [
            'account_level' => 0,
        ]);
    }

    public function showEsportPlayerRoles(Request $request)
    {
        return $this->showEsportPlayerPage($request, 'Player.Roles.allRoleData', [
            'account_level' => 0,
        ]);
    }

    public function showEsportPlayerRole(Request $request)
    {
        $role = $request->route('role');
        if (! (new RoleInputValidation)->passes('role', $role)) {
            return $this->redirectToPlayerPage($request, '/Role');
        }

        return $this->showEsportPlayerPage($request, 'Player.Roles.singleRoleData', [
            'role' => $role,
            'replayViewerAllowed' => false,
            'analyticsCutover' => config('analytics.cutover'),
            'replayDownloadBlocked' => false,
        ]);
    }

    public function showEsportPlayerLatestMatch(Request $request)
    {
        $battletag = $request->route('battletag');
        $blizz_id = $request->route('blizz_id');

        if (! $this->setPlayerLeague($request)) {
            return \Redirect::to('/Esports');
        }

        if (! ctype_digit((string) $blizz_id)) {
            return \Redirect::to($this->withLeagueQuery($this->leagueUrl()));
        }

        $s = $this->schema;
        $replayID = DB::table($s.'.replay')
            ->join($s.'.player', $s.'.player.replayID', '=', $s.'.replay.replayID')
            ->where($s.'.player.blizz_id', $blizz_id)
            ->when($this->esport === 'Other', fn ($query) => $query->where($s.'.replay.series', $this->series))
            ->orderByDesc($s.'.replay.game_date')
            ->value($s.'.replay.replayID');

        return \Redirect::to($this->withLeagueQuery($replayID
            ? $this->leagueUrl().'/Match/Single/'.$replayID
            : $this->playerPath($battletag, $blizz_id)));
    }

    private function redirectToPlayerPage(Request $request, string $page)
    {
        if (! $this->setPlayerLeague($request)) {
            return \Redirect::to('/Esports');
        }

        return \Redirect::to($this->withLeagueQuery($this->playerPath($request->route('battletag'), $request->route('blizz_id')).$page));
    }

    /** The main profile page's view, in its esports mode. */
    private function showEsportPlayerPage(Request $request, string $view, array $extra = [])
    {
        $battletag = $request->route('battletag');
        $blizz_id = $request->route('blizz_id');

        $input = [
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'season' => $request['season'],
            'division' => $request['division'],
            'team' => $request['team'],
        ];

        $validator = Validator::make($input, [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'season' => 'nullable|numeric',
            'division' => 'nullable|string',
            'team' => 'nullable|string',
        ]);

        if ($validator->fails() || ! $this->setPlayerLeague($request)) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            }

            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $esportfilters = ['season' => $input['season'], 'division' => $input['division'], 'team' => $input['team']];
        if (! $this->league()['division']) {
            $esportfilters['division'] = null;
        }
        if ($this->tournament) {
            $esportfilters['tournament'] = $this->tournament;
        }
        if ($this->series) {
            $esportfilters['series'] = $this->series;
            $esportfilters['seriesimage'] = $this->seriesIcon ??= Series::where('name', $this->series)->value('icon') ?? 'heroesprofilelogo.png';
        }

        return view($view)->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => true,
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => null,
            'esport' => $this->leagueDataInput()['esport'],
            'esportfilters' => $esportfilters,
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => [],
            'patreon' => false,
            'season' => null,
            'game_type' => null,
            'game_map' => null,
            ...$extra,
        ]);
    }

    public function getEsportPlayerFriendFoe(Request $request)
    {
        if ($error = $this->esportPlayerInput($request)) {
            return $error;
        }

        $games = $this->esportPlayerGames();
        $sides = $games->pluck('team', 'replayID');
        $heroData = $this->globalDataService->getHeroes()->keyBy('id');

        $players = ['friend' => [], 'enemy' => []];
        foreach ($this->esportOtherPlayers($games, true) as $row) {
            $type = $row->team == $sides[$row->replayID] ? 'friend' : 'enemy';
            $player = &$players[$type][$row->blizz_id];
            $player['battletag'] ??= explode('#', $row->battletag)[0];
            $player['wins'] ??= 0;
            $player['losses'] ??= 0;
            $player[$row->winner == 1 ? 'wins' : 'losses']++;
            $player['heroes'][$row->hero] = ($player['heroes'][$row->hero] ?? 0) + 1;
            unset($player);
        }

        $result = ['filters' => $this->esportPlayerFilterOptions()];
        foreach ($players as $type => $list) {
            uasort($list, fn ($a, $b) => ($b['wins'] + $b['losses']) <=> ($a['wins'] + $a['losses']));

            $result[$type] = collect(array_slice($list, 0, 50, true))->map(function ($player, $blizzId) use ($heroData) {
                arsort($player['heroes']);
                $heroId = array_key_first($player['heroes']);
                $gamesPlayed = $player['wins'] + $player['losses'];

                return [
                    'blizz_id' => $blizzId,
                    'battletag' => $player['battletag'],
                    'link' => $this->withLeagueQuery($this->playerPath($player['battletag'], $blizzId)),
                    'hero' => $heroData[$heroId]['name'],
                    'hero_games' => $player['heroes'][$heroId],
                    'heroData' => ['hero' => $heroData[$heroId]],
                    'total_wins' => $player['wins'],
                    'total_losses' => $player['losses'],
                    'total_games_played' => $gamesPlayed,
                    'win_rate' => round(($player['wins'] / $gamesPlayed) * 100, 2),
                ];
            })->values();
        }

        return $result;
    }

    public function getEsportPlayerMatchups(Request $request)
    {
        if ($error = $this->esportPlayerInput($request)) {
            return $error;
        }

        $games = $this->esportPlayerGames();
        $sides = $games->pluck('team', 'replayID');

        $rows = [];
        foreach ($this->globalDataService->getHeroes() as $hero) {
            $rows[$hero->id] = [
                'name' => $hero->name,
                'hero' => $hero,
                'ally_wins' => 0,
                'ally_losses' => 0,
                'ally_games_played' => 0,
                'enemy_wins' => 0,
                'enemy_losses' => 0,
                'enemy_games_played' => 0,
            ];
        }

        foreach ($this->esportOtherPlayers($games) as $row) {
            if (! isset($rows[$row->hero])) {
                continue;
            }
            $side = $row->team == $sides[$row->replayID] ? 'ally' : 'enemy';
            $rows[$row->hero][$side.'_'.($row->winner == 1 ? 'wins' : 'losses')]++;
        }

        foreach ($rows as &$row) {
            $row['ally_games_played'] = $row['ally_wins'] + $row['ally_losses'];
            $row['enemy_games_played'] = $row['enemy_wins'] + $row['enemy_losses'];
            if ($row['ally_games_played']) {
                $row['ally_win_rate'] = round(($row['ally_wins'] / $row['ally_games_played']) * 100, 2);
            }
            // The player's win rate against the hero, as the main page shows it
            if ($row['enemy_games_played']) {
                $row['enemy_win_rate'] = round(100 - ($row['enemy_wins'] / $row['enemy_games_played']) * 100, 2);
            }
        }
        unset($row);

        $topFiveAllyHeroes = collect($rows)
            ->filter(fn ($row) => $row['ally_games_played'] >= 5)
            ->sortByDesc('ally_win_rate')
            ->take(5)
            ->map(fn ($row) => [...$row, 'win_rate' => $row['ally_win_rate'], 'games_played' => $row['ally_games_played']])
            ->values();

        $topFiveEnemyHeroes = collect($rows)
            ->filter(fn ($row) => $row['enemy_games_played'] >= 5)
            ->sortBy('enemy_win_rate')
            ->take(5)
            ->map(fn ($row) => [...$row, 'win_rate' => round(100 - $row['enemy_win_rate'], 2), 'games_played' => $row['enemy_games_played']])
            ->values();

        return [
            'tabledata' => collect($rows)->sortBy('name')->values(),
            'top_five_heroes' => $topFiveAllyHeroes,
            'top_five_enemies' => $topFiveEnemyHeroes,
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    public function getEsportPlayerTalents(Request $request)
    {
        if ($error = $this->esportPlayerInput($request, ['hero' => ['required', new HeroInputValidation]])) {
            return $error;
        }

        $hero = $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']];
        $result = $this->esportPlayerTalentRows()->where('hero', $hero->id)->values();
        $talentData = HeroesDataTalent::all()->keyBy('talent_id');

        return [
            'talentData' => $this->getHeroTalentData($result, $talentData),
            'buildData' => $this->getHeroTalentBuildData($result, $hero, $talentData),
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    /** The player's most played build on each hero, shaped like the main page's builds list. */
    public function getEsportPlayerTalentBuilds(Request $request)
    {
        if ($error = $this->esportPlayerInput($request)) {
            return $error;
        }

        $heroData = $this->globalDataService->getHeroes()->keyBy('id');
        $talentData = HeroesDataTalent::all()->keyBy('talent_id');
        $rows = $this->esportPlayerTalentRows();

        $builds = [];
        foreach ($rows->groupBy('hero') as $heroId => $heroRows) {
            if (! isset($heroData[$heroId])) {
                continue;
            }
            $top = $this->getHeroTalentBuildData($heroRows, $heroData[$heroId], $talentData);
            if ($top) {
                $builds[$heroData[$heroId]->name] = ['ngs' => array_slice($top, 0, 1)];
            }
        }

        return [
            'heroes' => $rows->pluck('hero')->unique()->values(),
            'builds' => (object) $builds,
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    public function getEsportPlayerHeroes(Request $request)
    {
        if ($error = $this->esportPlayerInput($request)) {
            return $error;
        }

        $heroData = $this->globalDataService->getHeroes()->keyBy('id');
        $rows = $this->esportPlayerScoreRows()
            ->filter(fn ($row) => isset($heroData[$row->hero]))
            ->groupBy('hero')
            ->map(fn ($rows, $hero) => ['id' => $hero, 'name' => $heroData[$hero]->name, 'hero' => $heroData[$hero], ...$this->esportStatSummary($rows)])
            ->sortBy('name')
            ->values();

        return [
            'rows' => $rows,
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    public function getEsportPlayerMaps(Request $request)
    {
        if ($error = $this->esportPlayerInput($request)) {
            return $error;
        }

        $maps = Map::all()->keyBy('map_id');
        $rows = $this->esportPlayerScoreRows()
            ->filter(fn ($row) => isset($maps[$row->game_map]))
            ->groupBy('game_map')
            ->map(fn ($rows, $map) => ['id' => $map, 'name' => $maps[$map]->name, 'game_map' => $maps[$map], ...$this->esportStatSummary($rows)])
            ->sortBy('name')
            ->values();

        return [
            'rows' => $rows,
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    public function getEsportPlayerRoles(Request $request)
    {
        if ($error = $this->esportPlayerInput($request)) {
            return $error;
        }

        $rows = $this->esportPlayerScoreRows()
            ->groupBy('new_role')
            ->map(fn ($rows, $role) => ['id' => $role, 'name' => $role, ...$this->esportStatSummary($rows)])
            ->sortBy('name')
            ->values();

        return [
            'rows' => $rows,
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    /** The player's scoreboard line in each of their filtered games, with the hero, role, map and length. */
    private function esportPlayerScoreRows()
    {
        $s = $this->schema;

        return DB::table($s.'.scores')
            ->join($s.'.player', function ($join) use ($s) {
                $join->on($s.'.player.replayID', '=', $s.'.scores.replayID')
                    ->on($s.'.player.battletag', '=', $s.'.scores.battletag');
            })
            ->join($s.'.replay', $s.'.replay.replayID', '=', $s.'.scores.replayID')
            ->join('heroes', 'heroes.id', '=', $s.'.player.hero')
            ->whereIn($s.'.scores.replayID', $this->esportPlayerGames()->pluck('replayID')->all())
            ->where($s.'.player.blizz_id', $this->blizz_id)
            ->select([
                'heroes.new_role',
                $s.'.player.hero',
                $s.'.player.winner',
                $s.'.replay.game_map',
                $s.'.replay.game_length',
                ...array_map(fn ($column) => $s.'.scores.'.$column, self::SCORE_COLUMNS),
            ])
            ->get();
    }

    /** The match history, 100 games a page, in the rows the NGS profile's recent matches use. */
    public function getEsportPlayerMatchHistory(Request $request)
    {
        if ($error = $this->esportPlayerInput($request, ['pagination_page' => 'required|integer|min:1'])) {
            return $error;
        }

        $s = $this->schema;
        $levels = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];
        $teamsById = $this->league()['replay_teams'] === 'id';
        $replayTeams = $teamsById
            ? [$s.'.replay.team_0_id AS team_0_name', $s.'.replay.team_1_id AS team_1_name', $s.'.teams.team_id AS own_team_id']
            : [$s.'.replay.team_0_name', $s.'.replay.team_1_name'];

        $page = $this->esportPlayerGamesQuery()
            ->join($s.'.scores', function ($join) use ($s) {
                $join->on($s.'.scores.replayID', '=', $s.'.player.replayID')
                    ->on($s.'.scores.battletag', '=', $s.'.player.battletag');
            })
            ->leftJoin($s.'.talents', function ($join) use ($s) {
                $join->on($s.'.talents.replayID', '=', $s.'.player.replayID')
                    ->on($s.'.talents.battletag', '=', $s.'.player.battletag');
            })
            ->select([
                $s.'.replay.replayID',
                $s.'.replay.game_date',
                $s.'.replay.game_map',
                $s.'.replay.game',
                $s.'.replay.round',
                $s.'.replay.game_length',
                ...$replayTeams,
                $s.'.player.hero',
                $s.'.player.winner',
                $s.'.player.team',
                $s.'.teams.team_name AS team_label',
                $s.'.scores.kills',
                $s.'.scores.deaths',
                $s.'.scores.assists',
                ...array_map(fn ($level) => $s.'.talents.'.$level, $levels),
            ])
            ->orderByDesc($s.'.replay.game_date')
            ->paginate(100, ['*'], 'page', $request['pagination_page']);

        $heroData = $this->globalDataService->getHeroes()->keyBy('id');
        $maps = $this->globalDataService->getAllMapsKeyed();
        $talentData = $this->globalDataService->getAllTalentsKeyed();

        // CCL and HI replays hold team ids; the opponent's name comes from teams
        $opponentNames = $teamsById
            ? DB::table($s.'.teams')
                ->whereIn('team_id', collect($page->items())->flatMap(fn ($row) => [$row->team_0_name, $row->team_1_name])->filter()->unique()->values()->all())
                ->pluck('team_name', 'team_id')
            : null;

        $matches = collect($page->items())->map(function ($row) use ($heroData, $maps, $talentData, $levels, $teamsById, $opponentNames) {
            $match = [
                'replayID' => $row->replayID,
                'game_date' => $row->game_date,
                'game_map' => $maps[$row->game_map] ?? null,
                'game' => $row->game,
                'round' => $row->round,
                'winner' => (int) $row->winner,
                'hero' => $heroData[$row->hero] ?? null,
                'kills' => $row->kills,
                'deaths' => $row->deaths,
                'assists' => $row->assists,
                'game_length' => GameLength::played($row->game_length, null),
                'own_team' => $row->team_label,
                'opponent_team' => $teamsById
                    ? ($opponentNames[$row->team_0_name == $row->own_team_id ? $row->team_1_name : $row->team_0_name] ?? null)
                    : ($row->team_0_name === $row->team_label ? $row->team_1_name : $row->team_0_name),
                'own_side' => $row->team,
            ];
            foreach ($levels as $level) {
                $match[$level] = $row->$level && $talentData->has($row->$level) ? $talentData[$row->$level] : null;
            }

            return $match;
        })->all();

        return [
            'data' => $this->withMatchPlayers($matches, $heroData),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'filters' => $this->esportPlayerFilterOptions(),
        ];
    }

    /** One role's page, from the profile's own data filtered to the role. */
    public function getEsportPlayerRole(Request $request)
    {
        if ($error = $this->esportPlayerInput($request, ['role' => ['required', new RoleInputValidation]])) {
            return $error;
        }

        $role = $request['role'];
        $roleHeroes = $this->globalDataService->getHeroes()->where('new_role', $role)->pluck('id')->all();

        return $this->esportPlayerFilteredPage($request, ['role' => $role], fn ($games) => $games->whereIn('hero', $roleHeroes));
    }

    /** One map's page, from the profile's own data filtered to the map. */
    public function getEsportPlayerMap(Request $request)
    {
        if ($error = $this->esportPlayerInput($request, ['game_map' => ['required', new GameMapInputValidation]])) {
            return $error;
        }

        $mapIds = Map::where('name', $request['game_map'])->pluck('map_id')->all();

        return $this->esportPlayerFilteredPage($request, ['game_map' => $request['game_map']], fn ($games) => $games->whereIn('game_map', $mapIds));
    }

    /** One hero's page: the role page's data for the hero, plus its scoreboard totals and most played build. */
    public function getEsportPlayerHero(Request $request)
    {
        if ($error = $this->esportPlayerInput($request, ['hero' => ['required', new HeroInputValidation]])) {
            return $error;
        }

        $hero = $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']];
        $scoreRows = $this->esportPlayerScoreRows()->where('hero', $hero->id)->values();
        $talentRows = $this->esportPlayerTalentRows()->where('hero', $hero->id)->values();
        $topBuild = $this->getHeroTalentBuildData($talentRows, $hero, HeroesDataTalent::all()->keyBy('talent_id'))[0] ?? null;

        $page = $this->esportPlayerFilteredPage($request, ['hero' => $hero->name], fn ($games) => $games->where('hero', $hero->id));

        if (empty($page['games_played'])) {
            return $page;
        }

        return [
            ...$this->esportStatSummary($scoreRows),
            ...$page,
            'top_build' => $topBuild,
        ];
    }

    /**
     * A role or hero page's data: the profile's own numbers filtered to it, shaped
     * like the main site's, with the win rate in each season the player played it.
     */
    private function esportPlayerFilteredPage(Request $request, array $filter, callable $gameFilter): array
    {
        $filterOptions = $this->esportPlayerFilterOptions();
        $seasonHistory = $gameFilter($this->esportPlayerGames())
            ->groupBy('season')
            ->sortKeys()
            ->map(fn ($games, $season) => [
                'x_label' => 'Season '.$season,
                'win_rate' => round(($games->where('winner', 1)->count() / $games->count()) * 100, 2),
                'games_played' => $games->count(),
            ])
            ->values();

        $data = $this->getData(new Request([
            ...$this->leagueDataInput(),
            'battletag' => $request['battletag'],
            'blizz_id' => (string) $request['blizz_id'],
            'season' => $request['season'],
            'division' => $this->division,
            'team' => $request['team'],
            ...$filter,
        ]));

        if (isset($data['status'])) {
            return $data;
        }

        if (! $data['total_games']) {
            return ['filters' => $filterOptions, 'games_played' => 0];
        }

        return [
            'wins' => $data['wins'],
            'losses' => $data['losses'],
            'win_rate' => $data['win_rate'],
            'kdr' => $data['kdr'],
            'kda' => $data['kda'],
            'sum_kills' => (int) $data['kills'],
            'sum_deaths' => (int) $data['deaths'],
            'sum_assists' => (int) $data['assists'],
            'sum_takedowns' => (int) $data['takedowns'],
            'games_played' => $data['total_games'],
            'hero_data_all_heroes' => collect($data['heroes'])->sortByDesc('games_played')->values(),
            'hero_data_top_played' => $data['hero_top_three_most_played'],
            'hero_data_top_win_rate' => $data['hero_top_three_highest_win_rate'],
            'hero_data_top_lowest_win_rate' => $data['hero_top_three_lowest_win_rate'],
            'map_data' => collect($data['maps'])->sortByDesc('games_played')->values(),
            'map_data_top_played' => $data['map_top_three_most_played'],
            'map_data_top_win_rate' => $data['map_top_three_highest_win_rate'],
            'map_data_top_lowest_win_rate' => $data['map_top_three_lowest_win_rate'],
            'season_history' => $seasonHistory,
            'latestGames' => array_slice($data['matches'], 0, 7),
            'heroes_won_against' => $data['heroes_won_against'],
            'heroes_lost_against' => $data['heroes_lost_against'],
            'teams_played_for' => $data['teams_played_for'] ?? [],
            'filters' => $filterOptions,
        ];
    }

    private function esportPlayerInput(Request $request, array $rules = [])
    {
        $validator = Validator::make($request->all(), [
            'blizz_id' => 'required|integer',
            'battletag' => 'nullable|string',
            'season' => 'nullable|numeric',
            'division' => 'nullable|string',
            'team' => 'nullable|string',
            ...$rules,
        ]);

        if ($validator->fails() || ! $this->setPlayerLeague($request)) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $this->blizz_id = $request['blizz_id'];
        $this->battletag = $request['battletag'];
        $this->season = $request['season'];
        $this->division = $this->league()['division'] ? $request['division'] : null;
        $this->team = $request['team'];

        return null;
    }

    /** The player's rows joined to their team and the replay, under the league, season, division and team filters. */
    private function esportPlayerGamesQuery()
    {
        $s = $this->schema;

        return DB::table($s.'.player')
            ->join($s.'.teams', $s.'.teams.team_id', '=', $s.'.player.'.$this->league()['player_team'])
            ->join($s.'.replay', $s.'.replay.replayID', '=', $s.'.player.replayID')
            ->where($s.'.player.blizz_id', $this->blizz_id)
            ->when($this->esport === 'Other', fn ($query) => $query->where($s.'.replay.series', $this->series))
            ->when(! is_null($this->season), fn ($query) => $query->where($this->seasonColumn(), $this->season))
            ->when(! is_null($this->division), fn ($query) => $query->where($s.'.teams.division', $this->division))
            ->when(! is_null($this->team), fn ($query) => $query->where($s.'.teams.team_name', $this->team));
    }

    /** The player's filtered games, one row per game. */
    private function esportPlayerGames()
    {
        $s = $this->schema;

        return $this->esportPlayerGamesQuery()
            ->select([$s.'.player.replayID', $s.'.player.team', $s.'.player.hero', $s.'.player.winner', $this->seasonColumn(), $s.'.replay.game_map'])
            ->get()
            ->unique('replayID')
            ->values();
    }

    /** Everyone else in those games, on both sides. */
    private function esportOtherPlayers($games, bool $withBattletags = false)
    {
        $s = $this->schema;

        return DB::table($s.'.player')
            ->when($withBattletags, function ($query) use ($s) {
                return $query->join($s.'.battletags', $s.'.battletags.player_id', '=', $s.'.player.battletag')
                    ->addSelect($s.'.battletags.battletag');
            })
            ->addSelect([$s.'.player.replayID', $s.'.player.blizz_id', $s.'.player.team', $s.'.player.hero', $s.'.player.winner'])
            ->whereIn($s.'.player.replayID', $games->pluck('replayID')->all())
            ->where($s.'.player.blizz_id', '<>', $this->blizz_id)
            ->get();
    }

    private function esportPlayerTalentRows()
    {
        $s = $this->schema;

        return DB::table($s.'.talents')
            ->join($s.'.player', function ($join) use ($s) {
                $join->on($s.'.player.replayID', '=', $s.'.talents.replayID')
                    ->on($s.'.player.battletag', '=', $s.'.talents.battletag');
            })
            ->whereIn($s.'.talents.replayID', $this->esportPlayerGames()->pluck('replayID')->all())
            ->where($s.'.player.blizz_id', $this->blizz_id)
            ->select([
                $s.'.player.winner',
                $s.'.player.hero',
                $s.'.talents.level_one',
                $s.'.talents.level_four',
                $s.'.talents.level_seven',
                $s.'.talents.level_ten',
                $s.'.talents.level_thirteen',
                $s.'.talents.level_sixteen',
                $s.'.talents.level_twenty',
            ])
            ->get();
    }

    /** Every season, division (NGS only) and team the player has games in, for the page's filters. */
    private function esportPlayerFilterOptions(): array
    {
        $s = $this->schema;
        $hasDivision = $this->league()['division'];

        $rows = DB::table($s.'.player')
            ->join($s.'.teams', $s.'.teams.team_id', '=', $s.'.player.'.$this->league()['player_team'])
            ->when($this->esport === 'Other', function ($query) use ($s) {
                return $query->join($s.'.replay', $s.'.replay.replayID', '=', $s.'.player.replayID')
                    ->where($s.'.replay.series', $this->series);
            })
            ->where($s.'.player.blizz_id', $this->blizz_id)
            ->select([$this->seasonColumn(), ...($hasDivision ? [$s.'.teams.division'] : []), $s.'.teams.team_name'])
            ->distinct()
            ->get();

        $all = ['code' => null, 'name' => 'All'];

        return [
            'seasons' => [$all, ...$rows->pluck('season')->filter(fn ($season) => ! is_null($season))->unique()->sortDesc()->map(fn ($season) => ['code' => strval($season), 'name' => strval($season)])->values()->all()],
            'divisions' => $hasDivision
                ? [$all, ...$rows->pluck('division')->filter()->unique()->sort()->map(fn ($division) => ['code' => $division, 'name' => $division])->values()->all()]
                : null,
            'teams' => $rows->pluck('team_name')->unique()->sort()->values()->all(),
        ];
    }

    /** Win rate, KDA/KDR and the avg/max/sum of every score column, as the main Roles table reads them. */
    private function esportStatSummary($rows): array
    {
        $games = $rows->count();
        $wins = $rows->where('winner', 1)->count();
        $deaths = $rows->sum('deaths');

        $stats = [
            'wins' => $wins,
            'losses' => $games - $wins,
            'games_played' => $games,
            'win_rate' => round(($wins / $games) * 100, 2),
            'kda' => $deaths ? round($rows->sum('takedowns') / $deaths, 2) : $rows->sum('takedowns'),
            'kdr' => $deaths ? round($rows->sum('kills') / $deaths, 2) : $rows->sum('kills'),
            'combined_healing' => round($rows->avg('healing') + $rows->avg('self_healing'), 2),
            'avg_game_length' => round($rows->avg(fn ($row) => GameLength::played($row->game_length, null)), 2),
            'max_game_length' => $rows->max(fn ($row) => GameLength::played($row->game_length, null)),
            'sum_game_length' => $rows->sum(fn ($row) => GameLength::played($row->game_length, null)),
        ];

        foreach (self::SCORE_COLUMNS as $column) {
            $stats['avg_'.$column] = round($rows->avg($column), 2);
            $stats['max_'.$column] = $rows->max($column);
            $stats['sum_'.$column] = $rows->sum($column);
        }

        return $stats;
    }
}
