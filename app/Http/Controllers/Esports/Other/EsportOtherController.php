<?php

namespace App\Http\Controllers\Esports\Other;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Esports\Other\Series;
use App\Models\Esports\Other\Replay;
use App\Models\Esports\Other\Regions;
use App\Models\Esports\Other\OtherTeam;
use Illuminate\Support\Facades\Validator;
use App\Rules\HeroInputValidation;
use App\Rules\GameMapInputValidation;
use Illuminate\Support\Facades\DB;
use App\Models\Map;
use App\Rules\BattletagInputProhibitCharacters;
use App\Models\Esports\Other\Battletag;

class EsportOtherController extends Controller
{
    private $esport;

    private $schema;

    private $battletag;

    private $blizz_id;

    private $season;

    private $team;

    private $team_name;

    private $hero;

    private $game_map;


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
                'seriesimage' => Series::select("icon")->where("name", $series)->first()->icon,
                'team' => $team,
                'season' => $request['season'],
                'region' => $request['region'],
                'tournament' => $request['tournament'],
                'image' => 'logo.png',
                'division' => null,
            ]);
    }

    public function showWithEsport(Request $request, $series, $replayID)
    {
        $validationRules = [
            'series' => 'required|string',
            'replayID' => 'required|integer',
        ];

        $validator = Validator::make(compact('series', 'replayID'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('series', 'replayID'),
                'status' => 'failure to validate inputs',
            ];
        }

        return view('singleMatch')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'esport' => 'Other',
            'series' => $series,
            'replayID' => $replayID,
        ]);
    }

    public function showPlayer(Request $request, $series, $battletag, $blizz_id)
    {
        $validationRules = [
            'series' => 'required|string',
            'battletag' => 'required|string',
            'blizz_id' => 'required|numeric',
        ];

        $otherValidationRules = [
            'season' => 'nullable|numeric',
        ];

        $validator = Validator::make(compact('series', 'battletag', 'blizz_id'), $validationRules);

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
                'esport' => "Other",
                'series' => $series,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => $request['division'],
                'tournament' => $request['tournament'],
            ]);
    }


    public function showPlayerHero(Request $request, $series, $battletag, $blizz_id, $hero)
    {
        $validationRules = [
            'series' => 'required|string',
            'battletag' => 'required|string',
            'blizz_id' => 'required|numeric',
            'hero' => ['required', new HeroInputValidation],
        ];

        $otherValidationRules = [
            'season' => 'nullable|numeric',
        ];

        $validator = Validator::make(compact('series', 'battletag', 'blizz_id', 'hero'), $validationRules);

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
                'esport' => "Other",
                'series' => $series,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => null,
                'hero' => $hero,
                'tournament' => null,
            ]);
    }

    public function showPlayerMap(Request $request, $series, $battletag, $blizz_id, $game_map)
    {
        $validationRules = [
            'series' => 'required|string',
            'battletag' => 'required|string',
            'blizz_id' => 'required|numeric',
            'game_map' => ['required', new GameMapInputValidation],
        ];

        $otherValidationRules = [
            'season' => 'nullable|numeric',
        ];

        $validator = Validator::make(compact('series', 'battletag', 'blizz_id', 'game_map'), $validationRules);

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
                'esport' => "Other",
                'series' => $series,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'season' => $request['season'],
                'division' => null,
                'game_map' => $mapobject,
                'tournament' => null,
            ]);
    }

    public function playerSearch(Request $request)
    {
        $validationRules = [
            'userinput' => ['required', 'string', new BattletagInputProhibitCharacters],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $input = $request['userinput'];
        $input = str_replace(' ', '', $input);

        $data = null;
        if (strpos($input, '#') !== false) {
            $data = Battletag::select('blizz_id', 'battletag', 'region')
                ->where('battletag', $input)
                ->get();
        } else {
            $data = Battletag::select('blizz_id', 'battletag', 'region')
                ->where('battletag', 'LIKE', '%' . $input.'#%')
                ->get();
        }


        $firstBlizzId = collect($data)
            ->groupBy(fn ($item) => explode('#', $item->battletag)[0]) 
            ->map(fn ($group) => [
                'blizz_id' => $group->first()->blizz_id,
                'battletag' => explode('#', $group->first()->battletag)[0], 
                'region' => $group->first()->region,
            ])
            ->values(); 

    
        return $firstBlizzId;
    }

    public function getData(Request $request)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational,Other',
            'team' => 'nullable|string',
            'series' => 'nullable|string',
            'battletag' => 'nullable|string',
            'blizz_id' => 'nullable|string',
            'series' => 'nullable|string',
            'season' => 'nullable|numeric',
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'game_map' => ['sometimes', 'nullable',  new GameMapInputValidation],
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

        $this->season = $request['season'];

        $this->hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;
        $this->game_map = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id')->toArray() : null;

        $this->series = $request['series'];

        $this->team = $request['team'] ? OtherTeam::select("team_id")->where("team_name", $request["team"])->first()->team_id : null;
        $this->team_name = $request['team'];

        $this->schema .= '_ml';

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
                $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
            })
            ->join('heroesprofile.heroes', 'heroesprofile.heroes.id', '=', $this->schema.'.player.hero')
            ->select([
                $this->schema.'.teams.team_name',
                $this->schema.'.teams.image',
                DB::raw('SUBSTRING_INDEX('.$this->schema.'.battletags.battletag, "#", 1) as battletag'), 
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
                $this->schema.'.replay.team_0_name',
                $this->schema.'.replay.team_1_name',
                $this->schema.'.player.team_id',
                $this->schema.'.replay.season',
            ])

            ->when(! is_null($this->game_map), function ($query) {
                return $query->where($this->schema.'.replay.game_map', $this->game_map);
            })
            ->when(! is_null($this->team), function ($query) {
                return $query->where($this->schema.'.teams.team_id', $this->team);
            })
            ->when(! is_null($this->blizz_id), function ($query) {
                return $query->whereIn($this->schema.'.player.battletag', function($query) {
                    $query->select('player_id')
                          ->from($this->schema.'.battletags')
                          ->where('battletag', 'like', $this->battletag . "%");
                });
            })
            
            ->when(! is_null($this->hero), function ($query) {
                return $query->where($this->schema.'.player.hero', $this->hero);
            })
            ->when(! is_null($this->season), function ($query){
                return $query->where($this->schema.'.replay.season', $this->season);
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
                $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
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

            $playerlink = "/Esports/{$this->esport}/{$this->series}/Player/{$battletag}/{$blizzId}";

            if ($this->season) {
                $playerlink .= "?season={$this->season}";
            }

            $herolink = "/Esports/{$this->esport}/" . $this->series . "/Player/{$battletag}/{$blizzId}/Hero/{$heroData[$mostPlayedHero]['name']}";


            if ($this->season) {
                $herolink .= "?season={$this->season}";
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

            $team_0_name = $group[0]->team_0_name;
            $team_1_name = $group[0]->team_1_name;

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

            $link = "/Esports/{$this->esport}/{$this->series}/Player/{$this->battletag}/{$this->blizz_id}/Hero/{$heroData[$group[0]->hero]['name']}";


            if ($this->season) {
                $link .= "?season={$this->season}";
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

            $link = "/Esports/{$this->esport}/{$this->series}/Player/{$this->battletag}/{$this->blizz_id}/Map/{$maps[$group[0]->game_map]['name']}";


            if ($this->season) {
                $link .= "?season={$this->season}";
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
                'link' => $link,
            ];
        });

        $mapTopthreeHighestWinRate = $mapData->sortByDesc('win_rate')->take(3);
        $mapTopthreeLowestWinRate = $mapData->sortBy('win_rate')->take(3);
        $mapTopthreeMostPlayed = $mapData->sortByDesc('games_played')->take(3);

        $replayIDs = $results->pluck('replayID')->toArray();

        $mapBanData = null;
        $mapBanDataTransformed = [];
        $mapBanReturn = [];
        $counter = 0;

        $seasons = $seasons = DB::table('heroesprofile_ml.replay')
            ->select("season")
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->when($this->team, function ($query) {
                return $query->where(function ($query) {
                    $query->where('team_0_name', $this->team_name)
                        ->orWhere('team_1_name', $this->team_name);
                });
            })
            ->distinct()
            ->orderBy('season', 'desc')
            ->get()
            ->map(function ($season) {
                return ['code' => $season->season, 'name' => $season->season];
            })
            ->toArray();
    
        array_unshift($seasons, ['code' => null, 'name' => 'All']);
    

        $divisions = null;
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

        $image = '/images/EsportOther/' . Series::select("icon")->where("name", $this->series)->first()->icon;


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
                $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
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
                $join->on($this->schema.'.teams.team_id', '=', $this->schema.'.player.team_id');
            })
            ->select([
                $this->schema.'.replay.replayID',
                $this->schema.'.teams.team_name',
                $this->schema.'.teams.image',
            ])
            ->whereIn($this->schema.'.replay.replayID', $replayIDs)
            ->whereNot($this->schema.'.teams.team_id', $team)
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

            $image = '/images/EsportOther/' . Series::select("icon")->where("name", $series)->first()->icon;

            $returnData[$counter]['icon_url'] = $image;

            $returnData[$counter]['inputhover'] = 'Lost against team '.$enemyteam.' '.$count.' times ('.round((($count / (count($replayIDs))) * 100) * 5, 2).'% of all games lost as '.$team.')';

            $enemy_link = "/Esports/{$this->esport}/{$this->series}/Team/{$enemyteam}";


            if ($this->season) {
                $enemy_link .= "?season={$this->season}";
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
