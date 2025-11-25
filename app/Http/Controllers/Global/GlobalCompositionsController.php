<?php

namespace App\Http\Controllers\Global;

use App\Models\GameType;
use App\Models\GlobalCompositions;
use App\Models\MMRTypeID;
use App\Models\SeasonGameVersion;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalCompositionsController extends GlobalsInputValidationController
{
    public function show(Request $request)
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

        return view('Global.Compositions.compositionsStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'urlparameters' => $request->all(),
                'heroes' => $this->globalDataService->getHeroes(),

            ]);

    }

    public function getCompositionsData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'minimum_games' => 'required|integer',
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
        $statFilter = $request['statfilter'];
        $mirror = $request['mirror'];
        $talentbuildType = $request['talentbuildtype'];
        $minimumGames = $request['minimum_games'];

        $cacheKey = 'GlobalCompositionStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        if (config('app.env') !== 'production') {
            Cache::store('database')->forget($cacheKey);
        }

        $data = Cache::store('database')->remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $hero,
            $mirror,
            $region,
            $minimumGames
        ) {
            // Split game versions by ID (ID >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);

            $allData = collect();

            // Query old table if there are versions with ID < 250
            if (! empty($oldTableVersions)) {
                $oldData = GlobalCompositions::query()
                    ->select('composition_id', 'win_loss')
                    ->selectRaw('SUM(games_played) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->filterByHero($hero)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('composition_id', 'win_loss')
                    ->with(['composition'])
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });

                $allData = $allData->merge($oldData);
            }

            // Query new table if there are versions with ID >= 250
            if (! empty($newTableVersions)) {
                // Convert game version strings to IDs for the new table
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();

                if (! empty($newTableVersionIds)) {
                    $newData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_compositions as global_compositions')
                        ->select('global_compositions.composition_id', 'global_compositions.win_loss')
                        ->selectRaw('SUM(global_compositions.games_played) as games_played')
                        ->whereIn('global_compositions.game_version', $newTableVersionIds)
                        ->whereIn('global_compositions.game_type', $gameType)
                        ->when($leagueTier !== null && ! empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_compositions.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && ! empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_compositions.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && ! empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_compositions.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && ! empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_compositions.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && ! empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_compositions.hero_level', $heroLevel);
                        })
                        ->when($hero !== null, function ($query) use ($hero) {
                            return $query->where('global_compositions.hero', $hero);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_compositions.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_compositions.mirror', 0);
                        })
                        ->when($region !== null && ! empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_compositions.region', $region);
                        })
                        ->groupBy('global_compositions.composition_id', 'global_compositions.win_loss')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });

                    // Load composition relationships for new data
                    $compositionIds = $newData->pluck('composition_id')->unique();
                    $compositions = \App\Models\Composition::whereIn('composition_id', $compositionIds)->get()->keyBy('composition_id');

                    $newData = $newData->map(function ($item) use ($compositions) {
                        $item['composition'] = $compositions[$item['composition_id']] ?? null;

                        return $item;
                    });

                    $allData = $allData->merge($newData);
                }
            }

            // Combine and re-aggregate data from both tables
            $allData = $allData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }

                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['composition_id']) && isset($item['win_loss']);
            });

            $data = $allData->groupBy(function ($item) {
                return $item['composition_id'].'_'.$item['win_loss'];
            })->map(function ($group) {
                $first = $group->first();

                return [
                    'composition_id' => $first['composition_id'],
                    'win_loss' => $first['win_loss'],
                    'games_played' => $group->sum('games_played'),
                    'composition' => $first['composition'] ?? null,
                ];
            })->values();

            $roleData = MMRTypeID::all();
            $roleData = $roleData->keyBy('mmr_type_id');

            $totalGamesPlayed = $hero ? (collect($data)->sum('games_played')) : (collect($data)->sum('games_played') / 5);

            $filteredData = collect($data)
                ->groupBy('composition_id')
                ->map(function ($group) use ($totalGamesPlayed, $roleData, $minimumGames, $hero) {
                    $wins = $hero ? $group->where('win_loss', 1)->sum('games_played') : $group->where('win_loss', 1)->sum('games_played') / 5;
                    $losses = $hero ? $group->where('win_loss', 0)->sum('games_played') : $group->where('win_loss', 0)->sum('games_played') / 5;
                    $gamesPlayed = ($wins + $losses);

                    if ($gamesPlayed <= $minimumGames) {
                        return null;
                    }
                    $winRate = 0;
                    if ($gamesPlayed > 0) {
                        $winRate = (($wins) / $gamesPlayed) * 100;
                    }

                    $popularity = round(($gamesPlayed / $totalGamesPlayed) * 100, 2);

                    $compositionData = $group->first()['composition'];

                    $role_one = $roleData[$group->first()['composition']['role_one']];
                    $role_two = $roleData[$group->first()['composition']['role_two']];
                    $role_three = $roleData[$group->first()['composition']['role_three']];
                    $role_four = $roleData[$group->first()['composition']['role_four']];
                    $role_five = $roleData[$group->first()['composition']['role_five']];

                    return [
                        'composition_id' => $group->first()['composition_id'],
                        'wins' => $wins,
                        'losses' => $losses,
                        'win_rate' => round($winRate, 2),
                        'games_played' => round($gamesPlayed),
                        'popularity' => $popularity,
                        'role_one' => $role_one,
                        'role_two' => $role_two,
                        'role_three' => $role_three,
                        'role_four' => $role_four,
                        'role_five' => $role_five,
                    ];
                })
                ->filter()
                ->sortByDesc('win_rate')
                ->values()
                ->toArray();

            return $filteredData;

        });

        return $data;
    }

    public function getTopHeroData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'minimum_games' => 'required|integer',
            'composition_id' => 'required|integer',
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

        $gameTypeRecords = GameType::whereIn('short_name', $request['game_type'])->get();
        $gameType = $gameTypeRecords->pluck('type_id')->toArray();

        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $statFilter = $request['statfilter'];
        $mirror = $request['mirror'];
        $talentbuildType = $request['talentbuildtype'];
        $minimumGames = $request['minimum_games'];

        $compositionID = $request['composition_id'];

        $cacheKey = 'GlobalCompositionTopHeroes|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        if (config('app.env') !== 'production') {
            Cache::store('database')->forget($cacheKey);
        }

        $data = Cache::store('database')->remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $mirror,
            $region,
            $compositionID

        ) {
            // Split game versions by ID (ID >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);

            $allData = collect();

            // Query old table if there are versions with ID < 250
            if (! empty($oldTableVersions)) {
                $oldData = GlobalCompositions::query()
                    ->select('hero')
                    ->selectRaw('SUM(games_played) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->filterByCompositionID($compositionID)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('hero')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });

                $allData = $allData->merge($oldData);
            }

            // Query new table if there are versions with ID >= 250
            if (! empty($newTableVersions)) {
                // Convert game version strings to IDs for the new table
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();

                if (! empty($newTableVersionIds)) {
                    $newData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_compositions as global_compositions')
                        ->select('global_compositions.hero')
                        ->selectRaw('SUM(global_compositions.games_played) as games_played')
                        ->whereIn('global_compositions.game_version', $newTableVersionIds)
                        ->whereIn('global_compositions.game_type', $gameType)
                        ->when($leagueTier !== null && ! empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_compositions.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && ! empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_compositions.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && ! empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_compositions.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && ! empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_compositions.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && ! empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_compositions.hero_level', $heroLevel);
                        })
                        ->where('global_compositions.composition_id', $compositionID)
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_compositions.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_compositions.mirror', 0);
                        })
                        ->when($region !== null && ! empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_compositions.region', $region);
                        })
                        ->groupBy('global_compositions.hero')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });

                    $allData = $allData->merge($newData);
                }
            }

            // Combine and re-aggregate data from both tables
            $allData = $allData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }

                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['hero']);
            });

            $data = $allData->groupBy('hero')->map(function ($group) {
                return [
                    'hero' => $group->first()['hero'],
                    'games_played' => $group->sum('games_played'),
                ];
            })->values();

            $heroData = $this->globalDataService->getHeroes();
            $heroData = $heroData->keyBy('id');

            $data = $data->map(function ($item) use ($heroData) {
                $item['role'] = $heroData[$item->hero]['new_role'];
                $item['name'] = $heroData[$item->hero]['name'];
                $item['herodata'] = $heroData[$item->hero];

                return $item;
            });

            // Group the data by role
            $groupedData = $data->groupBy('role');

            // Filter and sort each group, then exclude empty groups
            $result = $groupedData->map(function ($group, $role) {
                return $group->sortByDesc('games_played')->values();
            })->reject(function ($group) {
                return $group->isEmpty();
            })->toArray();

            return $result;

        });

        return $data;
    }
}
