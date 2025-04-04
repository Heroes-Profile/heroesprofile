<?php

namespace App\Http\Controllers\Global;

use App\Models\GlobalHeroTalents;
use App\Models\HeroesDataTalent;
use App\Models\Replay;
use App\Models\Talent;
use App\Rules\HeroInputValidation;
use App\Rules\SelectedTalentInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalTalentBuilderController extends GlobalsInputValidationController
{
    public function show(Request $request, $hero = null)
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

        $validationRules = [
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
        ];

        $validator = Validator::make(['hero' => $hero], $validationRules);

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

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Global.Talents.globalTalentBuilder')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('multi'),
                'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
                'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                'defaultbuildtype' => $this->globalDataService->getDefaultBuildType(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'urlparameters' => $request->all(),
            ]);
    }

    public function getData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'hero' => ['required', new HeroInputValidation],
        ];

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['required', new HeroInputValidation],
            'selectedtalents' => ['sometimes', 'nullable', new SelectedTalentInputValidation],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero_name = $request['hero'];
        $selectedtalents = $request['selectedtalents'];

        $level_one = $selectedtalents[1];
        $level_four = $selectedtalents[4];
        $level_seven = $selectedtalents[7];
        $level_ten = $selectedtalents[10];
        $level_thirteen = $selectedtalents[13];
        $level_sixteen = $selectedtalents[16];
        $level_twenty = $selectedtalents[20];

        if (! $level_one && ! $level_four && ! $level_seven && ! $level_ten && ! $level_thirteen && ! $level_sixteen && ! $level_twenty) {
            $talents = HeroesDataTalent::where('hero_name', $hero_name)->orderBy('level', 'ASC')->orderBy('sort', 'ASC')->get();

            return ['talentData' => $this->formatTalentData($talents, [])];
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
        $cacheKey = 'GlobalTalentsBuilder|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        /*
        if (! env('Production')) {
            Cache::store('database')->forget($cacheKey);
        }
        */

        $data = Cache::remember($cacheKey, $this->globalDataService->calculateCacheTimeInMinutes($gameVersion), function () use (
            $talentData,
            $hero,
            $hero_name,
            $gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $region,
            $level_one,
            $level_four,
            $level_seven,
            $level_ten,
            $level_thirteen,
            $level_sixteen,
            $level_twenty,
        ) {

            $data = GlobalHeroTalents::query()
                ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
                ->select('win_loss', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
                ->selectRaw('SUM(games_played) AS games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByHero($hero)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->filterByRegion($region)
                ->when(! is_null($level_one), function ($query) use ($level_one) {
                    return $query->where('level_one', $level_one);
                })
                ->when(! is_null($level_four), function ($query) use ($level_four) {
                    return $query->where('level_four', $level_four);
                })
                ->when(! is_null($level_seven), function ($query) use ($level_seven) {
                    return $query->where('level_seven', $level_seven);
                })
                ->when(! is_null($level_ten), function ($query) use ($level_ten) {
                    return $query->where('level_ten', $level_ten);
                })
                ->when(! is_null($level_thirteen), function ($query) use ($level_thirteen) {
                    return $query->where('level_thirteen', $level_thirteen);
                })
                ->when(! is_null($level_sixteen), function ($query) use ($level_sixteen) {
                    return $query->where('level_sixteen', $level_sixteen);
                })
                ->when(! is_null($level_twenty), function ($query) use ($level_twenty) {
                    return $query->where('level_twenty', $level_twenty);
                })
                ->groupBy('win_loss', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            // ->toSql();
                ->get();

            $transformedData = [
                'level_one' => [],
                'level_four' => [],
                'level_seven' => [],
                'level_ten' => [],
                'level_thirteen' => [],
                'level_sixteen' => [],
                'level_twenty' => [],
            ];

            foreach ($data as $key => $value) {
                if (! array_key_exists($value['level_one'], $transformedData['level_one'])) {
                    $transformedData['level_one'][$value['level_one']]['wins'] = 0;
                    $transformedData['level_one'][$value['level_one']]['losses'] = 0;
                }

                if (! array_key_exists($value['level_four'], $transformedData['level_four'])) {
                    $transformedData['level_four'][$value['level_four']]['wins'] = 0;
                    $transformedData['level_four'][$value['level_four']]['losses'] = 0;
                }

                if (! array_key_exists($value['level_seven'], $transformedData['level_seven'])) {
                    $transformedData['level_seven'][$value['level_seven']]['wins'] = 0;
                    $transformedData['level_seven'][$value['level_seven']]['losses'] = 0;
                }

                if (! array_key_exists($value['level_ten'], $transformedData['level_ten'])) {
                    $transformedData['level_ten'][$value['level_ten']]['wins'] = 0;
                    $transformedData['level_ten'][$value['level_ten']]['losses'] = 0;
                }

                if (! array_key_exists($value['level_thirteen'], $transformedData['level_thirteen'])) {
                    $transformedData['level_thirteen'][$value['level_thirteen']]['wins'] = 0;
                    $transformedData['level_thirteen'][$value['level_thirteen']]['losses'] = 0;
                }

                if (! array_key_exists($value['level_sixteen'], $transformedData['level_sixteen'])) {
                    $transformedData['level_sixteen'][$value['level_sixteen']]['wins'] = 0;
                    $transformedData['level_sixteen'][$value['level_sixteen']]['losses'] = 0;
                }

                if (! array_key_exists($value['level_twenty'], $transformedData['level_twenty'])) {
                    $transformedData['level_twenty'][$value['level_twenty']]['wins'] = 0;
                    $transformedData['level_twenty'][$value['level_twenty']]['losses'] = 0;
                }

                if ($value['win_loss'] == 1) {
                    $transformedData['level_one'][$value['level_one']]['wins'] += $value['games_played'];
                    $transformedData['level_four'][$value['level_four']]['wins'] += $value['games_played'];
                    $transformedData['level_seven'][$value['level_seven']]['wins'] += $value['games_played'];
                    $transformedData['level_ten'][$value['level_ten']]['wins'] += $value['games_played'];
                    $transformedData['level_thirteen'][$value['level_thirteen']]['wins'] += $value['games_played'];
                    $transformedData['level_sixteen'][$value['level_sixteen']]['wins'] += $value['games_played'];
                    $transformedData['level_twenty'][$value['level_twenty']]['wins'] += $value['games_played'];
                } elseif ($value['win_loss'] == 0) {
                    $transformedData['level_one'][$value['level_one']]['losses'] += $value['games_played'];
                    $transformedData['level_four'][$value['level_four']]['losses'] += $value['games_played'];
                    $transformedData['level_seven'][$value['level_seven']]['losses'] += $value['games_played'];
                    $transformedData['level_ten'][$value['level_ten']]['losses'] += $value['games_played'];
                    $transformedData['level_thirteen'][$value['level_thirteen']]['losses'] += $value['games_played'];
                    $transformedData['level_sixteen'][$value['level_sixteen']]['losses'] += $value['games_played'];
                    $transformedData['level_twenty'][$value['level_twenty']]['losses'] += $value['games_played'];
                }
            }

            $buildReturnData = [
                'wins' => 0,
                'losses' => 0,
            ];

            $buildsData = GlobalHeroTalents::query()
                ->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id')
                ->select('win_loss')
                ->selectRaw('SUM(games_played) AS games_played')
                ->filterByGameVersion($gameVersion)
                ->filterByGameType($gameType)
                ->filterByHero($hero)
                ->filterByLeagueTier($leagueTier)
                ->filterByHeroLeagueTier($heroLeagueTier)
                ->filterByRoleLeagueTier($roleLeagueTier)
                ->filterByGameMap($gameMap)
                ->filterByHeroLevel($heroLevel)
                ->filterByRegion($region)
                ->when(! is_null($level_one), function ($query) use ($level_one) {
                    return $query->where('level_one', $level_one);
                })
                ->when(! is_null($level_four), function ($query) use ($level_four) {
                    return $query->where('level_four', $level_four);
                })
                ->when(! is_null($level_seven), function ($query) use ($level_seven) {
                    return $query->where('level_seven', $level_seven);
                })
                ->when(! is_null($level_ten), function ($query) use ($level_ten) {
                    return $query->where('level_ten', $level_ten);
                })
                ->when(! is_null($level_thirteen), function ($query) use ($level_thirteen) {
                    return $query->where('level_thirteen', $level_thirteen);
                })
                ->when(! is_null($level_sixteen), function ($query) use ($level_sixteen) {
                    return $query->where('level_sixteen', $level_sixteen);
                })
                ->when(! is_null($level_twenty), function ($query) use ($level_twenty) {
                    return $query->where('level_twenty', $level_twenty);
                })
                // ->toSql();
                ->groupBy('win_loss')
                ->get();

            $wins = ($buildReturnData['wins'] + $buildsData->where('win_loss', 1)->sum('games_played'));
            $losses = ($buildReturnData['losses'] + $buildsData->where('win_loss', 0)->sum('games_played'));
            $gamesPlayed = $wins + $losses;
            $winRate = $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0;

            $buildReturnData = [
                'level_one' => $level_one ? $talentData[$level_one] : null,
                'level_four' => $level_four ? $talentData[$level_four] : null,
                'level_seven' => $level_seven ? $talentData[$level_seven] : null,
                'level_ten' => $level_ten ? $talentData[$level_ten] : null,
                'level_thirteen' => $level_thirteen ? $talentData[$level_thirteen] : null,
                'level_sixteen' => $level_sixteen ? $talentData[$level_sixteen] : null,
                'level_twenty' => $level_twenty ? $talentData[$level_twenty] : null,

                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => $winRate,
            ];

            $talents = HeroesDataTalent::where('hero_name', $hero_name)->orderBy('level', 'ASC')->orderBy('sort', 'ASC')->limit(100)->get();
            $data = $this->formatTalentData($talents, $transformedData);

            return [
                'data' => $data,
                'buildReturnData' => $buildReturnData,
            ];
        });

        return [
            'talentData' => $data['data'],
            'buildData' => $data['buildReturnData'],
        ];
    }

    public function getReplayData(Request $request)
    {
        $validationRules = [
            'hero' => ['required', new HeroInputValidation],
        ];

        $validationRules = array_merge($this->globalsValidationRules($request['timeframe_type'], $request['timeframe']), [
            'hero' => ['required', new HeroInputValidation],
            'selectedtalents' => ['sometimes', 'nullable', new SelectedTalentInputValidation],
        ]);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero_name = $request['hero'];
        $selectedtalents = $request['selectedtalents'];

        $level_one = $selectedtalents[1];
        $level_four = $selectedtalents[4];
        $level_seven = $selectedtalents[7];
        $level_ten = $selectedtalents[10];
        $level_thirteen = $selectedtalents[13];
        $level_sixteen = $selectedtalents[16];
        $level_twenty = $selectedtalents[20];

        if (! $level_one && ! $level_four && ! $level_seven && ! $level_ten && ! $level_thirteen && ! $level_sixteen && ! $level_twenty) {
            $talents = HeroesDataTalent::where('hero_name', $hero_name)->orderBy('level', 'ASC')->orderBy('sort', 'ASC')->get();

            return ['talentData' => $this->formatTalentData($talents, [])];
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
        $cacheKey = 'GlobalTalentsBuilder|'.implode(',', \App\Models\SeasonGameVersion::select('id')->whereIn('game_version', $gameVersion)->pluck('id')->toArray()).'|'.hash('sha256', json_encode($request->all()));

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $replays = Replay::query()
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->select('replay.replayID')
            ->whereIn('game_version', $gameVersion)
            ->whereIn('game_type', $gameType)
            ->where('hero', $hero)
            ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                return $query->whereIn('game_map', $gameMap);
            })
            ->when(! is_null($region), function ($query) use ($region) {
                return $query->whereIn('region', $region);
            })
            ->orderByDesc('replay.game_date')
        // ->toSql();
            ->limit(10000)
            ->get();

        $replayIDs = $replays->pluck('replayID')->toArray();

        $replayTalents = Talent::select('replayID', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty')
            ->whereIn('talents.replayID', $replayIDs)
            ->when(! is_null($level_one), function ($query) use ($level_one) {
                return $query->where('level_one', $level_one);
            })
            ->when(! is_null($level_four), function ($query) use ($level_four) {
                return $query->where('level_four', $level_four);
            })
            ->when(! is_null($level_seven), function ($query) use ($level_seven) {
                return $query->where('level_seven', $level_seven);
            })
            ->when(! is_null($level_ten), function ($query) use ($level_ten) {
                return $query->where('level_ten', $level_ten);
            })
            ->when(! is_null($level_thirteen), function ($query) use ($level_thirteen) {
                return $query->where('level_thirteen', $level_thirteen);
            })
            ->when(! is_null($level_sixteen), function ($query) use ($level_sixteen) {
                return $query->where('level_sixteen', $level_sixteen);
            })
            ->when(! is_null($level_twenty), function ($query) use ($level_twenty) {
                return $query->where('level_twenty', $level_twenty);
            })
            ->limit(50)
            ->get();

        $replayTalents = $replayTalents->map(function ($replay) use ($talentData) {
            $replay['level_one'] = $replay['level_one'] ? $talentData[$replay['level_one']] : null;
            $replay['level_four'] = $replay['level_four'] ? $talentData[$replay['level_four']] : null;
            $replay['level_seven'] = $replay['level_seven'] ? $talentData[$replay['level_seven']] : null;
            $replay['level_ten'] = $replay['level_ten'] ? $talentData[$replay['level_ten']] : null;
            $replay['level_thirteen'] = $replay['level_thirteen'] ? $talentData[$replay['level_thirteen']] : null;
            $replay['level_sixteen'] = $replay['level_sixteen'] ? $talentData[$replay['level_sixteen']] : null;
            $replay['level_twenty'] = $replay['level_twenty'] ? $talentData[$replay['level_twenty']] : null;

            return $replay;
        });

        return $replayTalents;
    }

    private function formatTalentData($talents, $transformedData)
    {
        $groupedTalents = $talents->groupBy('level');

        $groupedTalents->each(function ($group, $level) use ($transformedData) {
            $group->each(function ($talent) use ($level, $transformedData) {

                $totalGames = null;
                $wins = null;

                if (count($transformedData) > 0) {
                    switch ($level) {
                        case 1:
                            if (array_key_exists($talent->talent_id, $transformedData['level_one'])) {
                                $wins = $transformedData['level_one'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_one'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                        case 4:
                            if (array_key_exists($talent->talent_id, $transformedData['level_four'])) {
                                $wins = $transformedData['level_four'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_four'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                        case 7:
                            if (array_key_exists($talent->talent_id, $transformedData['level_seven'])) {
                                $wins = $transformedData['level_seven'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_seven'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                        case 10:
                            if (array_key_exists($talent->talent_id, $transformedData['level_ten'])) {
                                $wins = $transformedData['level_ten'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_ten'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                        case 13:
                            if (array_key_exists($talent->talent_id, $transformedData['level_thirteen'])) {
                                $wins = $transformedData['level_thirteen'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_thirteen'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                        case 16:
                            if (array_key_exists($talent->talent_id, $transformedData['level_sixteen'])) {
                                $wins = $transformedData['level_sixteen'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_sixteen'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                        case 20:
                            if (array_key_exists($talent->talent_id, $transformedData['level_twenty'])) {
                                $wins = $transformedData['level_twenty'][$talent->talent_id]['wins'];
                                $losses = $transformedData['level_twenty'][$talent->talent_id]['losses'];
                                $totalGames = $wins + $losses;
                            }
                            break;
                    }
                }

                $winRate = $totalGames ? $totalGames > 0 ? ($wins / $totalGames) * 100 : null : null;

                $talent->win_rate = round($winRate, 2);
                $talent->games_played = $totalGames;
            });
        });

        return $groupedTalents;
    }
}
