<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroChange;
use App\Models\GlobalHeroStats;
use App\Models\GlobalHeroStatsBans;
use App\Models\SeasonGameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalHeroStatsController extends GlobalsInputValidationController
{
    public function show(Request $request)
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

        return view('Global.Hero.globalHeroStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'heroes' => $this->globalDataService->getHeroes(),
                'urlparameters' => $request->all(),
            ]);
    }

    public function getGlobalHeroData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = $this->globalsValidationRules($request['timeframe_type'], $request['timeframe']);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $gameVersion = $this->globalDataService->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->globalDataService->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $mirror = $request['mirror'];

        $region = $this->globalDataService->getRegionFilterValues($request['region']);

        $statFilter = $request['statfilter'];
        $hero = $this->globalDataService->getHeroFilterValue($request['hero']);
        $role = $request['role'];

        $cacheKey = 'GlobalHeroStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        
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
            $region,
            $statFilter,
            $hero,
            $role,
            $mirror
        ) {
            // Split game versions by patch ID (patch >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);
            
            $allData = collect();
            
            // Query old table if there are versions with patch < 250
            if (!empty($oldTableVersions)) {
                $oldData = GlobalHeroStats::query()
                    ->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                    ->select('heroes.name', 'heroes.short_name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'heroes.new_role as role')
                    ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                    ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                        return $query->selectRaw("SUM(global_hero_stats.$statFilter) as total_filter_type");
                    })
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('global_hero_stats.hero', 'global_hero_stats.win_loss')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });

                $allData = $allData->merge($oldData);
            }
            

            // Query new table if there are versions with patch >= 250
            if (!empty($newTableVersions)) {
                // Convert game version strings to IDs for the new table
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();
                
                if (empty($newTableVersionIds)) {
                    // If no IDs found, skip querying the new table
                    $newData = collect();
                } else {
                    $newData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_stats as global_hero_stats')
                        ->join('heroesprofile.heroes as heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                        ->select('heroes.name', 'heroes.short_name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'heroes.new_role as role')
                        ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                        ->when($statFilter !== 'win_rate', function ($query) use ($statFilter) {
                            return $query->selectRaw("SUM(global_hero_stats.$statFilter) as total_filter_type");
                        })
                        ->whereIn('global_hero_stats.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_stats.game_type', $gameType)
                        ->when($leagueTier !== null, function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_stats.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null, function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_stats.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null, function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_stats.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null, function ($query) use ($gameMap) {
                            return $query->whereIn('global_hero_stats.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null, function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_stats.hero_level', $heroLevel);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_hero_stats.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_hero_stats.mirror', 0);
                        })
                        ->when($region !== null, function ($query) use ($region) {
                            return $query->whereIn('global_hero_stats.region', $region);
                        })
                        ->groupBy('global_hero_stats.hero', 'global_hero_stats.win_loss')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item; // DB query builder returns stdClass, so cast to array
                        });
                }

                $allData = $allData->merge($newData);
            }

            // Combine and re-aggregate data from both tables
            // Ensure all items are arrays and filter out any invalid items
            $allData = $allData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }
                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['hero_id']) && isset($item['win_loss']);
            });
            
            $data = $allData->groupBy(function ($item) {
                return $item['hero_id'] . '_' . $item['win_loss'];
            })->map(function ($group) use ($statFilter) {
                $first = $group->first();
                return [
                    'name' => $first['name'],
                    'short_name' => $first['short_name'],
                    'hero_id' => $first['hero_id'],
                    'win_loss' => $first['win_loss'],
                    'role' => $first['role'],
                    'games_played' => $group->sum('games_played'),
                    'total_filter_type' => $statFilter !== 'win_rate' ? $group->sum('total_filter_type') : null,
                ];
            })->values();
            
            // Sort by hero name and win_loss
            $data = $data->sortBy([
                ['name', 'asc'],
                ['win_loss', 'asc']
            ])->values();

            // Query ban data - split by version ID
            $banAllData = collect();
            
            // Query old table for bans
            if (!empty($oldTableVersions)) {
                $banOldData = GlobalHeroStatsBans::query()
                    ->join('heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                    ->select('heroes.name', 'heroes.id as hero_id')
                    ->selectRaw('SUM(global_hero_stats_bans.bans) as bans')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->filterByRegion($region)
                    ->groupBy('global_hero_stats_bans.hero')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });
                
                $banAllData = $banAllData->merge($banOldData);
            }
            
            // Query new table for bans
            if (!empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($newTableVersionIds)) {
                    $banNewData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_stats_bans as global_hero_stats_bans')
                        ->join('heroesprofile.heroes as heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                        ->select('heroes.name', 'heroes.id as hero_id')
                        ->selectRaw('SUM(global_hero_stats_bans.bans) as bans')
                        ->whereIn('global_hero_stats_bans.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_stats_bans.game_type', $gameType)
                        ->when($leagueTier !== null && !empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_stats_bans.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && !empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_stats_bans.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && !empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_stats_bans.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && !empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_hero_stats_bans.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && !empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_stats_bans.hero_level', $heroLevel);
                        })
                        ->when($region !== null && !empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_stats_bans.region', $region);
                        })
                        ->groupBy('global_hero_stats_bans.hero')
                        ->get()
                        ->map(function ($item) {
                            return (array) $item;
                        });
                    
                    $banAllData = $banAllData->merge($banNewData);
                }
            }
            
            // Combine and re-aggregate ban data
            $banAllData = $banAllData->map(function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }
                return $item;
            })->filter(function ($item) {
                return is_array($item) && isset($item['hero_id']);
            });
            
            $banData = $banAllData->groupBy('hero_id')->map(function ($group) {
                $first = $group->first();
                return [
                    'name' => $first['name'],
                    'hero_id' => $first['hero_id'],
                    'bans' => $group->sum('bans'),
                ];
            })->values();

            $changeData = null;

            if ($this->checkIfChange($gameVersion, $region, $gameType, $gameMap, $leagueTier, $heroLeagueTier, $roleLeagueTier, $heroLevel)) {
                $changeData = GlobalHeroChange::query()
                    ->join('heroesprofile.heroes', 'heroesprofile.heroes.id', '=', 'global_hero_change.hero')
                    ->select('heroes.name', 'heroes.id as hero_id', 'win_rate as change_win_rate')
                    ->filterByGameVersion($this->calculateGameVersionsForHeroChange($gameVersion))
                    ->filterByGameType($gameType)
                    // ->toSql();
                    ->get();
            }

            return $this->combineData($data, $statFilter, $banData, $changeData, $hero, $role);
        });

        return $data;
    }

    private function calculateGameVersionsForHeroChange($version)
    {
        try {
            [$major, $minor, $patch, $build] = explode('.', $version[0]);
            $major = (int) $major;
            $minor = (int) $minor;
            $patch = (int) $patch;
            $build = (int) $build;

            $previousVersion = SeasonGameVersion::where('valid_globals', 1)
                ->where(function ($query) use ($major, $minor, $patch, $build) {
                    $query->where('major', '<', $major)
                        ->orWhere(function ($q) use ($major, $minor) {
                            $q->where('major', '=', $major)
                                ->where('minor', '<', $minor);
                        })
                        ->orWhere(function ($q) use ($major, $minor, $patch) {
                            $q->where('major', '=', $major)
                                ->where('minor', '=', $minor)
                                ->where('patch', '<', $patch);
                        })
                        ->orWhere(function ($q) use ($major, $minor, $patch, $build) {
                            $q->where('major', '=', $major)
                                ->where('minor', '=', $minor)
                                ->where('patch', '=', $patch)
                                ->where('build', '<', $build);
                        });
                })
                ->orderByDesc('major')
                ->orderByDesc('minor')
                ->orderByDesc('patch')
                ->orderByDesc('build')
                ->first();

            return $previousVersion ? [$previousVersion->game_version] : ['2.55.3.90670'];
        } catch (\Exception $e) {
            return ['2.55.3.90670'];
        }
    }

    private function combineData($data, $statFilter, $banData, $changeData, $hero, $role)
    {
        $totalGamesPlayed = collect($data)->sum('games_played') / 10;

        $sortBy = $statFilter == 'win_rate' ? 'win_rate' : 'total_filter_type';
        $combinedData = collect($data)->groupBy('name')->map(function ($group) use ($banData, $changeData, $totalGamesPlayed) {
            $firstItem = $group->first();

            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $winRate = 0;
            if ($gamesPlayed > 0) {
                $winRate = ($wins / $gamesPlayed) * 100;
            }

            $matchingBan = $banData->where('name', $firstItem['name'])->first();
            $bans = $matchingBan ? round($matchingBan['bans']) : 0; // Round the bans value

            $banRate = 0;
            if ($bans > 0) {
                $banRate = ($bans / $totalGamesPlayed) * 100;
            }

            $changeWinRate = 0;

            if ($changeData) {
                $matchingChange = $changeData->where('name', $firstItem['name'])->first();
                if ($matchingChange) {
                    $changeWinRate = $matchingChange['change_win_rate'];
                }
            }

            $popularity = (($gamesPlayed + $bans) / $totalGamesPlayed) * 100;
            $pickRate = ($gamesPlayed / $totalGamesPlayed) * 100;

            $adjustedPickRate = (($gamesPlayed / $totalGamesPlayed) * 100) / (100 - $banRate);
            $influence = round((($winRate / 100) - 0.5) * ($adjustedPickRate * 10000));

            $confidenceInterval = $this->globalDataService->calculateWinRateConfidenceInterval($wins, $gamesPlayed);

            $statFilterTotal = $group->sum('total_filter_type');

            return [
                'name' => $firstItem['name'],
                'short_name' => $firstItem['short_name'],
                'hero_id' => $firstItem['hero_id'],
                'role' => $firstItem['role'],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => round($winRate, 2),
                'ban_rate' => round($banRate, 2),
                'win_rate_change' => round($winRate - $changeWinRate, 2),
                'popularity' => round($popularity, 2),
                'pick_rate' => round($pickRate, 2),
                'influence' => $influence,
                'confidence_interval' => round($confidenceInterval, 2),
                'total_filter_type' => $gamesPlayed > 0 ? round($statFilterTotal / $gamesPlayed, 2) : 0,
            ];
        })->sortByDesc($sortBy)->values()->toArray();

        if ($hero) {
            $combinedData = collect($combinedData)->filter(function ($item) use ($hero) {
                return $item['hero_id'] == $hero;
            })->values();
        }

        if ($role) {
            $combinedData = collect($combinedData)->filter(function ($item) use ($role) {
                return $item['role'] == $role;
            })->values();
        }

        $combinedCollection = collect($combinedData);

        $positiveInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] > 0;
        });

        $negativeInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] < 0;
        });

        $positiveWinRateChangeCollection = $combinedCollection->filter(function ($item) {
            return $item['win_rate_change'] > 0;
        });

        $negativeWinRateChangeCollection = $combinedCollection->filter(function ($item) {
            return $item['win_rate_change'] < 0;
        });

        $averageWinRate = $combinedCollection->avg('win_rate');
        $averageConfidenceInterval = $combinedCollection->avg('confidence_interval');
        $averagePopularity = $combinedCollection->avg('popularity');
        $averagePickRate = $combinedCollection->avg('pick_rate');
        $averageBanRate = $combinedCollection->avg('ban_rate');
        $averagePositiveInfluence = $positiveInfluenceCollection->avg('influence');
        $averageNegativeInfluence = $negativeInfluenceCollection->avg('influence');
        $averagePositiveWinRateChange = $positiveWinRateChangeCollection->avg('win_rate_change');
        $averageNegativeWinRateChange = $negativeWinRateChangeCollection->avg('win_rate_change');
        $averageGamesPlayed = count($combinedCollection) > 0 ? $combinedCollection->sum('games_played') / count($combinedCollection) : 0;
        $averageTotalFilterType = $combinedCollection->avg('total_filter_type');

        return [
            'average_win_rate' => round($averageWinRate, 2),
            'average_confidence_interval' => round($averageConfidenceInterval, 2),
            'average_popularity' => round($averagePopularity, 2),
            'average_pick_rate' => round($averagePickRate, 2),
            'average_ban_rate' => round($averageBanRate, 2),
            'average_positive_influence' => round($averagePositiveInfluence, 0),
            'average_negative_influence' => round($averageNegativeInfluence, 0),
            'average_positive_win_rate_change' => round($averagePositiveWinRateChange, 2),
            'average_negative_win_rate_change' => round($averageNegativeWinRateChange, 2),
            'average_games_played' => round($averageGamesPlayed, 0),
            'averaege_total_filter_type' => round($averageTotalFilterType, 0),
            'data' => $combinedData,
        ];
    }

    // 🤮
    private function checkIfChange($timeframe, $region, $game_type, $map, $league_tier, $hero_league_tier, $role_league_tier, $hero_level)
    {
        if (
            count($timeframe) == 1 &&
            count($game_type) == 1 &&
            empty($region) &&
            empty($map) &&
            empty($league_tier) &&
            empty($hero_league_tier) &&
            empty($role_league_tier) &&
            empty($hero_level)
        ) {
            return true;
        }

        return false;
    }
}
