<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroStackSize;
use App\Models\SeasonGameVersion;
use App\Rules\HeroInputValidation;
use App\Rules\PartyCombinationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalPartyStatsController extends GlobalsInputValidationController
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

        return view('Global.Party.globalPartyStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'heroes' => $this->globalDataService->getHeroes(),
                'urlparameters' => $request->all(),
            ]);
    }

    public function getPartyStats(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes

        // return response()->json($request->all());

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'teamoneparty' => ['sometimes', 'nullable', new PartyCombinationRule],
            'teamtwoparty' => ['sometimes', 'nullable', new PartyCombinationRule],
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
        $heropartysize = $request['heropartysize'];

        $teamoneparty = $request['teamoneparty'];
        $teamtwoparty = $request['teamtwoparty'];

        $cacheKey = 'GlobalPartyStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        // return $cacheKey;

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
            $hero,
            $teamoneparty,
            $teamtwoparty
        ) {
            // Split game versions by ID (ID >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);
            
            $allData = collect();
            
            // Query old table if there are versions with ID < 250
            if (!empty($oldTableVersions)) {
                $oldData = GlobalHeroStackSize::query()
                    ->select('team_ally_stack_value', 'team_enemy_stack_value')
                    ->selectRaw('SUM(games_played) as games_played')
                    ->selectRaw('SUM(IF(win_loss = 1, games_played, 0)) AS wins')
                    ->selectRaw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->filterByHero($hero)
                    ->filterByAllyStackSize($teamoneparty)
                    ->filterByEnemyStackSize($teamtwoparty)
                    ->groupBy('team_ally_stack_value', 'team_enemy_stack_value')
                    ->get()
                    ->map(function ($item) {
                        return $item->toArray();
                    });
                
                $allData = $allData->merge($oldData);
            }
            
            // Query new table if there are versions with ID >= 250
            if (!empty($newTableVersions)) {
                $newTableVersionIds = SeasonGameVersion::whereIn('game_version', $newTableVersions)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($newTableVersionIds)) {
                    $newData = DB::connection('heroesprofile')
                        ->table('heroesprofile_globals.global_hero_stack_size as global_hero_stack_size')
                        ->select('global_hero_stack_size.team_ally_stack_value', 'global_hero_stack_size.team_enemy_stack_value')
                        ->selectRaw('SUM(global_hero_stack_size.games_played) as games_played')
                        ->selectRaw('SUM(IF(global_hero_stack_size.win_loss = 1, global_hero_stack_size.games_played, 0)) AS wins')
                        ->selectRaw('SUM(IF(global_hero_stack_size.win_loss = 0, global_hero_stack_size.games_played, 0)) AS losses')
                        ->whereIn('global_hero_stack_size.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_stack_size.game_type', $gameType)
                        ->when($leagueTier !== null && !empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_stack_size.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && !empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_stack_size.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && !empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_stack_size.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && !empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_hero_stack_size.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && !empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_stack_size.hero_level', $heroLevel);
                        })
                        ->when($mirror == 1, function ($query) {
                            return $query->whereIn('global_hero_stack_size.mirror', [0, 1]);
                        }, function ($query) {
                            return $query->where('global_hero_stack_size.mirror', 0);
                        })
                        ->when($region !== null && !empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_stack_size.region', $region);
                        })
                        ->when($hero !== null, function ($query) use ($hero) {
                            return $query->where('global_hero_stack_size.hero', $hero);
                        })
                        ->when($teamoneparty !== null && !empty($teamoneparty), function ($query) use ($teamoneparty) {
                            return $query->where('global_hero_stack_size.team_ally_stack_value', $teamoneparty);
                        })
                        ->when($teamtwoparty !== null && !empty($teamtwoparty), function ($query) use ($teamtwoparty) {
                            return $query->where('global_hero_stack_size.team_enemy_stack_value', $teamtwoparty);
                        })
                        ->groupBy('global_hero_stack_size.team_ally_stack_value', 'global_hero_stack_size.team_enemy_stack_value')
                        ->orderBy('global_hero_stack_size.team_ally_stack_value', 'asc')
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
                return is_array($item) && isset($item['team_ally_stack_value']) && isset($item['team_enemy_stack_value']);
            });
            
            $data = $allData->groupBy(function ($item) {
                return $item['team_ally_stack_value'] . '_' . $item['team_enemy_stack_value'];
            })->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'team_ally_stack_value' => $first['team_ally_stack_value'],
                    'team_enemy_stack_value' => $first['team_enemy_stack_value'],
                    'games_played' => $group->sum('games_played'),
                    'wins' => $group->sum('wins'),
                    'losses' => $group->sum('losses'),
                ];
            })->values();

            $returnData = [];
            $total = 0;

            $divideValue = 1;

            if (! $hero) {
                $divideValue = 10;
            }

            $party_combinations = [
                '00005' => '5 Solo',
                '00023' => '1 Double, 3 Solo',
                '00041' => '2 Double, 1 Solo',
                '00302' => '1 Triple, 2 Solo',
                '00320' => '1 Triple, 1 Double',
                '04001' => '1 Quad, 1 Solo',
                '50000' => '1 team of 5',
            ];

            foreach ($data as $row) {
                $total += $row->wins + $row->losses;

                $comboType = '';
                switch ($row->team_ally_stack_value) {
                    case '00005':
                        $comboType = 'solo';
                        break;
                    case '00023':
                        $comboType = 'double';
                        break;
                    case '00041':
                        $comboType = 'double_double';
                        break;
                    case '00302':
                        $comboType = 'triple';
                        break;
                    case '00320':
                        $comboType = 'triple_double';
                        break;
                    case '04001':
                        $comboType = 'quadruple';
                        break;
                    case '50000':
                        $comboType = 'quintuple';
                        break;
                }

                if ($comboType) {
                    $combo = $row->team_ally_stack_value.'|'.$row->team_enemy_stack_value;

                    $returnData[$comboType][$combo]['ally_combo'] = $row->team_ally_stack_value;
                    $returnData[$comboType][$combo]['enemy_combo'] = $row->team_enemy_stack_value;
                    $returnData[$comboType][$combo]['wins'] = round($row->wins / $divideValue);
                    $returnData[$comboType][$combo]['losses'] = round($row->losses / $divideValue);

                    $gamesPlayed = $returnData[$comboType][$combo]['wins'] + $returnData[$comboType][$combo]['losses'];

                    $returnData[$comboType][$combo]['win_rate'] = $gamesPlayed ? round(($returnData[$comboType][$combo]['wins'] / $gamesPlayed) * 100, 2) : 0;

                    $returnData[$comboType][$combo]['stack_size_name'] = $party_combinations[$row->team_enemy_stack_value] ?? '5 Solo';
                }
            }

            return $returnData;

        });

        return $data;
    }
}
