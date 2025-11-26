<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroStats;
use App\Models\GlobalHeroStatsBans;
use App\Models\Map;
use App\Models\SeasonGameVersion;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalHeroMapStatsController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null)
    {
        $validationRules = $this->globalValidationRulesURLParam($request['timeframe_type'], $request['timeframe']);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'errors' => $validator->errors()->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        if (! is_null($hero)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation],
            ];

            $validator = Validator::make(['hero' => $hero], $validationRules);

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
        }
        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Global.Hero.Map.globalHeroMapStats')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'urlparameters' => $request->all(),
            ]);
    }

    public function getHeroStatMapData(Request $request)
    {

        // return response()->json($request->all());
        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['required', new HeroInputValidation],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero = $this->globalDataService->getHeroFilterValue($request['hero']);
        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $mirror = $request['mirror'];

        $cacheKey = 'GlobalHeroMapStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        // return $cacheKey;

        if (config('app.env') !== 'production') {
            Cache::store('database')->forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
            $hero,
            $gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $heroLevel,
            $mirror,
            $region
        ) {
            // Split game versions by ID (ID >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);

            // Query GlobalHeroStats data
            $allData = collect();

            // Query old table for stats
            if (! empty($oldTableVersions)) {
                $oldData = GlobalHeroStats::query()
                    ->join('heroesprofile.heroes', 'heroesprofile.heroes.id', '=', 'global_hero_stats.hero')
                    ->join('heroesprofile.maps', 'heroesprofile.maps.map_id', '=', 'global_hero_stats.game_map')
                    ->select('heroesprofile.heroes.name', 'heroesprofile.heroes.id as hero_id', 'global_hero_stats.win_loss', 'heroesprofile.maps.map_id')
                    ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->filterByHero($hero)
                    ->groupBy('win_loss')
                    ->groupBy('hero')
                    ->groupBy('map_id')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });

                $allData = $allData->merge($oldData);
            }

            // Query new table for stats
            if (! empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();

                if (! empty($newTableVersionIds)) {
                    $newData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_stats as global_hero_stats')
                        ->join('heroesprofile.heroes as heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                        ->join('heroesprofile.maps as maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                        ->select('heroes.name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'map_id')
                        ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                        ->whereIn('global_hero_stats.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_stats.game_type', $gameType)
                        ->where('global_hero_stats.hero', $hero)
                        ->when($leagueTier !== null && ! empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_stats.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && ! empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_stats.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && ! empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_stats.role_league_tier', $roleLeagueTier);
                        })
                        ->when($heroLevel !== null && ! empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_stats.hero_level', $heroLevel);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_hero_stats.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_hero_stats.mirror', 0);
                        })
                        ->when($region !== null && ! empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_stats.region', $region);
                        })
                        ->groupBy('global_hero_stats.win_loss')
                        ->groupBy('global_hero_stats.hero')
                        ->groupBy('global_hero_stats.game_map')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });

                    $allData = $allData->merge($newData);
                }
            }

            // Combine and re-aggregate stats data
            $allData = $allData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }

                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['hero_id']) && isset($item['win_loss']) && isset($item['map_id']);
            });

            $data = $allData->groupBy(function ($item) {
                return $item['hero_id'].'_'.$item['win_loss'].'_'.$item['map_id'];
            })->map(function ($group) {
                $first = $group->first();

                return [
                    'name' => $first['name'],
                    'hero_id' => $first['hero_id'],
                    'win_loss' => $first['win_loss'],
                    'map_id' => $first['map_id'],
                    'games_played' => $group->sum('games_played'),
                ];
            })->values();

            // Query gamesPlayedPerMap
            $gamesPlayedAllData = collect();

            if (! empty($oldTableVersions)) {
                $gamesPlayedOldData = GlobalHeroStats::query()
                    ->select('game_map')
                    ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('game_map')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });

                $gamesPlayedAllData = $gamesPlayedAllData->merge($gamesPlayedOldData);
            }

            if (! empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();

                if (! empty($newTableVersionIds)) {
                    $gamesPlayedNewData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_stats as global_hero_stats')
                        ->select('global_hero_stats.game_map')
                        ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                        ->whereIn('global_hero_stats.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_stats.game_type', $gameType)
                        ->when($leagueTier !== null && ! empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_stats.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && ! empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_stats.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && ! empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_stats.role_league_tier', $roleLeagueTier);
                        })
                        ->when($heroLevel !== null && ! empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_stats.hero_level', $heroLevel);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_hero_stats.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_hero_stats.mirror', 0);
                        })
                        ->when($region !== null && ! empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_stats.region', $region);
                        })
                        ->groupBy('global_hero_stats.game_map')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });

                    $gamesPlayedAllData = $gamesPlayedAllData->merge($gamesPlayedNewData);
                }
            }

            $gamesPlayedAllData = $gamesPlayedAllData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }

                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['game_map']);
            });

            $gamesPlayedPerMap = $gamesPlayedAllData->groupBy('game_map')->map(function ($group) {
                $first = $group->first();

                return [
                    'game_map' => $first['game_map'],
                    'games_played' => $group->sum('games_played'),
                ];
            })->values();

            // Query ban data
            $banAllData = collect();

            if (! empty($oldTableVersions)) {
                $banOldData = GlobalHeroStatsBans::query()
                    ->join('heroesprofile.heroes as heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                    ->join('heroesprofile.maps as maps', 'maps.map_id', '=', 'global_hero_stats_bans.game_map')
                    ->select('heroes.name', 'heroes.id as hero_id', 'map_id')
                    ->selectRaw('SUM(global_hero_stats_bans.bans) as bans')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByHeroLevel($heroLevel)
                    ->filterByHero($hero)
                    ->filterByRegion($region)
                    ->groupBy('hero')
                    ->groupBy('map_id')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });

                $banAllData = $banAllData->merge($banOldData);
            }

            if (! empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();

                if (! empty($newTableVersionIds)) {
                    $banNewData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_stats_bans as global_hero_stats_bans')
                        ->join('heroesprofile.heroes as heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                        ->join('heroesprofile.maps as maps', 'maps.map_id', '=', 'global_hero_stats_bans.game_map')
                        ->select('heroes.name', 'heroes.id as hero_id', 'map_id')
                        ->selectRaw('SUM(global_hero_stats_bans.bans) as bans')
                        ->whereIn('global_hero_stats_bans.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_stats_bans.game_type', $gameType)
                        ->where('global_hero_stats_bans.hero', $hero)
                        ->when($leagueTier !== null && ! empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_stats_bans.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && ! empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_stats_bans.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && ! empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_stats_bans.role_league_tier', $roleLeagueTier);
                        })
                        ->when($heroLevel !== null && ! empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_stats_bans.hero_level', $heroLevel);
                        })
                        ->when($region !== null && ! empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_stats_bans.region', $region);
                        })
                        ->groupBy('global_hero_stats_bans.hero')
                        ->groupBy('global_hero_stats_bans.game_map')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });

                    $banAllData = $banAllData->merge($banNewData);
                }
            }

            $banAllData = $banAllData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }

                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['hero_id']) && isset($item['map_id']);
            });

            $banData = $banAllData->groupBy(function ($item) {
                return $item['hero_id'].'_'.$item['map_id'];
            })->map(function ($group) {
                $first = $group->first();

                return [
                    'name' => $first['name'],
                    'hero_id' => $first['hero_id'],
                    'map_id' => $first['map_id'],
                    'bans' => $group->sum('bans'),
                ];
            })->values();

            return $this->combineData($gameType, $hero, $data, $gamesPlayedPerMap, $banData);
        });

        return $data;

    }

    private function combineData($gameType, $hero, $data, $gamesPlayedPerMap, $banData)
    {
        // Ensure data is in array format
        $data = $data->map(function ($item) {
            if (is_object($item)) {
                return (array) $item;
            }

            return $item;
        });

        $filteredData = $data->filter(function ($item) use ($hero) {
            return isset($item['hero_id']) && $item['hero_id'] === $hero;
        });

        $mapData = Map::all();
        $mapData = $mapData->keyBy('map_id');

        $totalGamesPlayed = $filteredData->sum('games_played');

        return collect($filteredData)
            ->groupBy(function ($data) {
                return $data['name'].$data['map_id'];
            })
        // For some reason every hero has 1 game played in ARAM that isnt an ARAM map...something odd in my backend code
            ->filter(function ($group) use ($gameType, $mapData) {
                if (count($gameType) == 1 && $gameType[0] == '6') {
                    $firstItem = $group->first();

                    return $mapData[$firstItem['map_id']]['type'] == 'ARAM';
                }

                // Keep all other maps
                return true;
            })
            ->map(function ($group) use ($banData, $gamesPlayedPerMap, $mapData, $totalGamesPlayed) {
                $firstItem = $group->first();

                $wins = $group->where('win_loss', 1)->sum('games_played');
                $losses = $group->where('win_loss', 0)->sum('games_played');
                $gamesPlayed = $wins + $losses;

                $matchingBan = $banData->first(function ($value) use ($firstItem) {
                    return $value['name'] === $firstItem['name'] && $value['map_id'] === $firstItem['map_id'];
                });
                $bans = $matchingBan ? round($matchingBan['bans']) : 0;

                $gameMapData = $gamesPlayedPerMap->where('game_map', $firstItem['map_id'])->first();
                $totalGamesForThisMap = $gameMapData ? ($gameMapData['games_played'] / 10) : 0;

                return [
                    'name' => $mapData[$firstItem['map_id']]['name'],
                    'map' => $mapData[$firstItem['map_id']],
                    'win_rate' => $gamesPlayed != 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                    'games_played' => $gamesPlayed,
                    'ban_rate' => $totalGamesForThisMap != 0 ? round(($bans / $totalGamesForThisMap) * 100, 2) : 0,
                    'total_games_played' => $totalGamesPlayed,
                ];
            })
            ->sortByDesc('win_rate')
            ->values()
            ->toArray();
    }
}
