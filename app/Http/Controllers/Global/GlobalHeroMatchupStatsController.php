<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeromatchupsAlly;
use App\Models\GlobalHeromatchupsEnemy;
use App\Models\SeasonGameVersion;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalHeroMatchupStatsController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null, $allyenemy = null)
    {
        $validationRules = $this->globalValidationRulesURLParam($request['timeframe_type'], $request['timeframe']);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'errors' => $validator->errors()->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        if (! is_null($hero) && ! is_null($allyenemy)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation],
                'allyenemy' => ['required', new HeroInputValidation],
            ];

            $validator = Validator::make(['hero' => $hero, 'allyenemy' => $allyenemy], $validationRules);

            if ($validator->fails()) {
                if (env('Production')) {
                    return \Redirect::to('/');
                } else {
                    return [
                        'data' => $request->all(),
                        'errors' => $validator->errors()->all(),
                        'status' => 'failure to validate inputs',
                    ];
                }
            }
        }

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Global.Matchups.globalMatchupsStats')->with([
            'heroes' => $this->globalDataService->getHeroes(),
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'userinput' => $userinput,
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
            'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
            'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
            'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
            'urlparameters' => $request->all(),
        ]);
    }

    public function getHeroMatchupData(Request $request)
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
        $statFilter = $request['statfilter'];
        $mirror = $request['mirror'];

        $role = $request['role'];

        $cacheKey = 'GlobalMatchupStats|'.hash('sha256', json_encode($gameVersion).'|'.json_encode($request->all()));

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
            $gameMap,
            $heroLevel,
            $mirror,
            $region,
            $role
        ) {
            $heroData = $this->globalDataService->getHeroes()->keyBy('id');

            // Split game versions by ID (ID >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);
            
            // Query ally data
            $allyAllData = collect();
            
            // Query old table for ally
            if (!empty($oldTableVersions)) {
                $allyOldData = GlobalHeromatchupsAlly::query()
                    ->select('ally', 'win_loss')
                    ->selectRaw('SUM(games_played) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByHero($hero)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('ally')
                    ->groupBy('win_loss')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });
                
                $allyAllData = $allyAllData->merge($allyOldData);
            }
            
            // Query new table for ally
            if (!empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($newTableVersionIds)) {
                    $allyNewData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_matchups_ally as global_hero_matchups_ally')
                        ->select('global_hero_matchups_ally.ally', 'global_hero_matchups_ally.win_loss')
                        ->selectRaw('SUM(global_hero_matchups_ally.games_played) as games_played')
                        ->whereIn('global_hero_matchups_ally.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_matchups_ally.game_type', $gameType)
                        ->where('global_hero_matchups_ally.hero', $hero)
                        ->when($leagueTier !== null && !empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_matchups_ally.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && !empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_matchups_ally.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && !empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_matchups_ally.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && !empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_hero_matchups_ally.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && !empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_matchups_ally.hero_level', $heroLevel);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_hero_matchups_ally.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_hero_matchups_ally.mirror', 0);
                        })
                        ->when($region !== null && !empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_matchups_ally.region', $region);
                        })
                        ->groupBy('global_hero_matchups_ally.ally')
                        ->groupBy('global_hero_matchups_ally.win_loss')
                        ->orderBy('global_hero_matchups_ally.ally')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });
                    
                    $allyAllData = $allyAllData->merge($allyNewData);
                }
            }
            
            // Combine and re-aggregate ally data
            $allyAllData = $allyAllData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }
                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['ally']) && isset($item['win_loss']);
            });
            
            $allyData = $allyAllData->groupBy(function ($item) {
                return $item['ally'] . '_' . $item['win_loss'];
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'ally' => $first['ally'],
                    'win_loss' => $first['win_loss'],
                    'games_played' => $group->sum('games_played'),
                ];
            })->values();
            
            $allyData = $this->combineData($allyData, 'ally', $hero, $role, $heroData);

            // Query enemy data
            $enemyAllData = collect();
            
            // Query old table for enemy
            if (!empty($oldTableVersions)) {
                $enemyOldData = GlobalHeromatchupsEnemy::query()
                    ->select('enemy', 'win_loss')
                    ->selectRaw('SUM(games_played) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByHero($hero)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('enemy')
                    ->groupBy('win_loss')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });
                
                $enemyAllData = $enemyAllData->merge($enemyOldData);
            }
            
            // Query new table for enemy
            if (!empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($newTableVersionIds)) {
                    $enemyNewData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_matchups_enemy as global_hero_matchups_enemy')
                        ->select('global_hero_matchups_enemy.enemy', 'global_hero_matchups_enemy.win_loss')
                        ->selectRaw('SUM(global_hero_matchups_enemy.games_played) as games_played')
                        ->whereIn('global_hero_matchups_enemy.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_matchups_enemy.game_type', $gameType)
                        ->where('global_hero_matchups_enemy.hero', $hero)
                        ->when($leagueTier !== null && !empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_matchups_enemy.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && !empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_matchups_enemy.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && !empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_matchups_enemy.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && !empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_hero_matchups_enemy.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && !empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_matchups_enemy.hero_level', $heroLevel);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_hero_matchups_enemy.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_hero_matchups_enemy.mirror', 0);
                        })
                        ->when($region !== null && !empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_matchups_enemy.region', $region);
                        })
                        ->groupBy('global_hero_matchups_enemy.enemy')
                        ->groupBy('global_hero_matchups_enemy.win_loss')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });
                    
                    $enemyAllData = $enemyAllData->merge($enemyNewData);
                }
            }
            
            // Combine and re-aggregate enemy data
            $enemyAllData = $enemyAllData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }
                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['enemy']) && isset($item['win_loss']);
            });
            
            $enemyData = $enemyAllData->groupBy(function ($item) {
                return $item['enemy'] . '_' . $item['win_loss'];
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'enemy' => $first['enemy'],
                    'win_loss' => $first['win_loss'],
                    'games_played' => $group->sum('games_played'),
                ];
            })->values();
            
            $enemyData = $this->combineData($enemyData, 'enemy', $hero, $role, $heroData);

            $allyDataKeyed = collect($allyData)->keyBy(function ($item) {
                return $item['hero']['name'];
            });

            $enemyDataKeyed = collect($enemyData)->keyBy(function ($item) {
                return $item['hero']['name'];
            });

            // Combine the collections
            $combinedData = $allyDataKeyed->map(function ($allyItem, $heroName) use ($enemyDataKeyed) {
                $enemyItem = $enemyDataKeyed->get($heroName);
                if ($enemyItem) {
                    return [
                        'ally' => $allyItem,
                        'enemy' => $enemyItem,
                    ];
                }

                return [
                    'ally' => $allyItem,
                    'enemy' => null,
                ];
            })->sortKeys()->values()->toArray();

            return ['ally' => $allyData, 'enemy' => $enemyData, 'combined' => $combinedData];
        });

        $heroData = $this->globalDataService->getAllHeroesGlobalWinRates($request);
        $heroDataByName = collect($heroData)->keyBy('name');

        foreach ($data['combined'] as &$combinedEntry) {
            if (isset($heroDataByName[$combinedEntry['ally']['hero']['name']])) {
                $combinedEntry['ally']['stats'] = $heroDataByName[$combinedEntry['ally']['hero']['name']];
            }

            if (isset($heroDataByName[$combinedEntry['enemy']['hero']['name']])) {
                $combinedEntry['enemy']['stats'] = $heroDataByName[$combinedEntry['enemy']['hero']['name']];
            }
        }

        return $data;
    }

    private function combineData($data, $type, $heroID, $role, $heroData)
    {

        $combinedData = collect($data)->groupBy($type)->map(function ($group) use ($type, $heroData, $role) {
            $firstItem = $group->first();
            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $winRate = $gamesPlayed != 0 ? ($wins / $gamesPlayed) * 100 : 0;
            $winRate = $type == 'ally' ? $winRate : 100 - $winRate;

            if ($role && ($heroData[$firstItem[$type]]['new_role'] != $role)) {
                return null;
            }

            return [
                'hero' => $heroData[$firstItem[$type]],
                'role' => $heroData[$firstItem[$type]]['new_role'],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => round($winRate, 2),
                'hovertext' => $type == 'ally' ? 'Won while on a team with '.$heroData[$firstItem[$type]]['name'].' '.round($winRate, 2).'%'.' of the time.' : 'Lost against a team with '.$heroData[$firstItem[$type]]['name'].' '.round($winRate, 2).'%'.' of games.',
            ];
        })->filter()->sortByDesc('win_rate')->values()->toArray();

        $notFound = [];
        $existingHeroIds = collect($combinedData)->pluck('hero.id')->flip();

        foreach ($heroData as $hero) {
            if ($role && ($hero['new_role'] != $role)) {
                continue;
            }

            if (! isset($existingHeroIds[$hero->id]) && $heroID != $hero->id) {
                $notFound[] = $hero;
            }
        }

        foreach ($notFound as $hero) {
            $combinedData[] = [
                'hero' => $hero,
                'wins' => 0,
                'losses' => 0,
                'games_played' => 0,
                'win_rate' => 0.0,
                'hovertext' => '',
            ];
        }

        return $combinedData;

    }
}
