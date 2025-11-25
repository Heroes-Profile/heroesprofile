<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroDraftOrder;
use App\Models\SeasonGameVersion;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalDraftController extends GlobalsInputValidationController
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
                        'errors' => $validator->errors()->all(),
                        'status' => 'failure to validate inputs',
                    ];
                }
            }
        }
        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Global.Draft.globalDraftStats')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => ['sl'], // $this->globalDataService->getGameTypeDefault('multi'),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'urlparameters' => $request->all(),
            ]);
    }

    public function getDraftData(Request $request)
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

        $cacheKey = 'GlobalDraftStats|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

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
            $gameMap,
            $heroLevel,
            $region,
        ) {
            // Split game versions by ID (ID >= 250 goes to new table)
            [$oldTableVersions, $newTableVersions] = $this->splitGameVersionsByPatch($gameVersion, 250);

            $allData = collect();

            // Query old table if there are versions with ID < 250
            if (! empty($oldTableVersions)) {
                $oldData = GlobalHeroDraftOrder::query()
                    ->select('pick_number', 'win_loss')
                    ->selectRaw('SUM(count) as games_played')
                    ->filterByGameVersion($oldTableVersions)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->filterByRegion($region)
                    ->filterByHero($hero)
                    ->groupBy('pick_number', 'win_loss')
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
                        ->table('heroesprofile_globals.global_hero_draft_order as global_hero_draft_order')
                        ->select('global_hero_draft_order.pick_number', 'global_hero_draft_order.win_loss')
                        ->selectRaw('SUM(global_hero_draft_order.count) as games_played')
                        ->whereIn('global_hero_draft_order.game_version', $newTableVersionIds)
                        ->whereIn('global_hero_draft_order.game_type', $gameType)
                        ->when($leagueTier !== null && ! empty($leagueTier), function ($query) use ($leagueTier) {
                            return $query->whereIn('global_hero_draft_order.league_tier', $leagueTier);
                        })
                        ->when($heroLeagueTier !== null && ! empty($heroLeagueTier), function ($query) use ($heroLeagueTier) {
                            return $query->whereIn('global_hero_draft_order.hero_league_tier', $heroLeagueTier);
                        })
                        ->when($roleLeagueTier !== null && ! empty($roleLeagueTier), function ($query) use ($roleLeagueTier) {
                            return $query->whereIn('global_hero_draft_order.role_league_tier', $roleLeagueTier);
                        })
                        ->when($gameMap !== null && ! empty($gameMap), function ($query) use ($gameMap) {
                            return $query->whereIn('global_hero_draft_order.game_map', $gameMap);
                        })
                        ->when($heroLevel !== null && ! empty($heroLevel), function ($query) use ($heroLevel) {
                            return $query->whereIn('global_hero_draft_order.hero_level', $heroLevel);
                        })
                        ->when($region !== null && ! empty($region), function ($query) use ($region) {
                            return $query->whereIn('global_hero_draft_order.region', $region);
                        })
                        ->where('global_hero_draft_order.hero', $hero)
                        ->groupBy('global_hero_draft_order.pick_number', 'global_hero_draft_order.win_loss')
                        ->orderBy('global_hero_draft_order.pick_number')
                        ->orderBy('global_hero_draft_order.win_loss')
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
                return is_array($item) && isset($item['pick_number']) && isset($item['win_loss']);
            });

            $data = $allData->groupBy(function ($item) {
                return $item['pick_number'].'_'.$item['win_loss'];
            })->map(function ($group) {
                $first = $group->first();

                return [
                    'pick_number' => $first['pick_number'],
                    'win_loss' => $first['win_loss'],
                    'games_played' => $group->sum('games_played'),
                ];
            })->values();

            $totalGamesPlayed = $data->sum('games_played');

            $data = $data->groupBy('pick_number')->map(function ($group) use ($totalGamesPlayed) {
                $firstItem = $group->first();

                $wins = round($group->where('win_loss', 1)->sum('games_played'));
                $losses = round($group->where('win_loss', 0)->sum('games_played'));
                $gamesPlayed = $wins + $losses;

                $winRate = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0;

                $popularity = $totalGamesPlayed > 0 ? round(($gamesPlayed / $totalGamesPlayed) * 100, 2) : 0;

                return [
                    'pick_number' => $firstItem['pick_number'],
                    'wins' => $wins,
                    'losses' => $losses,
                    'games_played' => $gamesPlayed,
                    'popularity' => $popularity,
                    'win_rate' => $winRate,
                ];
            })->values()->toArray();

            return $data;
        });

        return $data;
    }
}
