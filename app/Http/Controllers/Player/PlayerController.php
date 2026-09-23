<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Battletag;
use App\Models\GameType;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataUD;
use App\Models\Player;
use App\Models\PlayerStatsCache;
use App\Models\ProfilePage;
use App\Models\Replay;
use App\Rules\DateInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

        if (request()->has('season')) {
            $validationRules['season'] = ['sometimes', 'nullable', new SeasonInputValidation];
        }

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region'), $validationRules);

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

        $season = $request['season'];

        return view('Player.player')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'season' => $season,
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),

            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
        ]);
    }

    public function getPlayerData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation],
            'start_date' => ['sometimes', 'nullable', new DateInputValidation],
            'end_date' => ['sometimes', 'nullable', new DateInputValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $battletag = $request['battletag'];
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameTypeIds = $request['game_type'] ? GameType::whereIn('short_name', (array) $request['game_type'])->pluck('type_id')->sort()->values() : collect();
        $allGameTypeIds = GameType::whereIn('short_name', ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'])->pluck('type_id');
        // profile_page rows are one game type, or null for every type
        if ($gameTypeIds->isEmpty() || $allGameTypeIds->diff($gameTypeIds)->isEmpty()) {
            $game_type = null;
        } elseif ($gameTypeIds->count() === 1) {
            $game_type = $gameTypeIds->first();
        } else {
            $game_type = $gameTypeIds->all();
        }
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $seasonIds = collect((array) $request['season'])
            ->reject(fn ($season) => is_null($season) || $season === 'All')
            ->sort()
            ->values();

        $latestReplayID = Replay::select('replay.replayID')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            // Same exclusion the profile query applies when no game type is chosen, or a
            // newest custom game would look like new data on every load.
            ->when(! is_null($game_type), function ($query) use ($game_type) {
                return $query->whereIn('game_type', (array) $game_type);
            }, function ($query) {
                return $query->where('game_type', '<>', 0);
            })
            ->tap(function ($query) use ($seasonIds, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($query, $seasonIds->all(), $startDate, $endDate);
            })
            ->orderBy('replayID', 'DESC')
            ->limit(1)
            ->first()
            ->replayID ?? null;

        // profile_page only holds totals for one season (or all) and one game type (or all)
        if ($startDate || $endDate || $seasonIds->count() > 1 || is_array($game_type)) {
            $cachedData = $this->getCustomProfile($blizz_id, $region, $game_type, $seasonIds->all(), $startDate, $endDate, $latestReplayID);

            return $cachedData ? $this->formatProfile($cachedData, $blizz_id, $region, $battletag) : null;
        }

        $season = $seasonIds->first();

        $cachedData = ProfilePage::filterByBlizzID($blizz_id)
            ->filterByRegion($region)
            ->where('game_type', $game_type)
            ->where('season', $season)
            ->first();

        if ($cachedData && is_null($cachedData->weekday_data)) {
            $cachedData->delete();
            $cachedData = null;
        }

        if ($cachedData && is_null($cachedData->latest_replayID)) {
            $cachedData->delete();
            $cachedData = null;
        }
        if (! $cachedData) {
            $this->calculateProfile($blizz_id, $region, $game_type, $season);

            $cachedData = ProfilePage::filterByBlizzID($blizz_id)
                ->filterByRegion($region)
                ->where('game_type', $game_type)
                ->where('season', $season)
                ->first();
        }

        if (($latestReplayID && $cachedData) && $cachedData->latest_replayID < $latestReplayID) {
            $this->calculateProfile($blizz_id, $region, $game_type, $season, $cachedData);
            $cachedData = ProfilePage::filterByBlizzID($blizz_id)
                ->filterByRegion($region)
                ->where('game_type', $game_type)
                ->where('season', $season)
                ->first();
        }

        if ($cachedData) {
            if ($this->refreshPendingRatings($cachedData, $blizz_id)) {
                $cachedData->save();
            }

            return $this->formatProfile($cachedData, $blizz_id, $region, $battletag);
        }

        return null;
    }

    private function formatProfile($cachedData, $blizz_id, $region, $battletag)
    {
        $isOwner = Auth::check()
            && Auth::user()->blizz_id == $blizz_id
            && Auth::user()->region == $region;

        $cachedData->weekday_data = $isOwner ? $cachedData->weekday_data : null;

        return $this->formatCache($cachedData, $blizz_id, $region, $battletag);
    }

    /**
     * Several seasons or a date range. Computed in full and kept in player_stats_cache
     * until the player has a newer replay in that window.
     */
    private function getCustomProfile($blizz_id, $region, $game_type, $seasons, $startDate, $endDate, $latestReplayID)
    {
        $paramsHash = hash('sha256', json_encode([
            'page' => 'profile',
            'blizz_id' => $blizz_id,
            'region' => $region,
            'game_type' => $game_type,
            'season' => $seasons,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $dbCache = PlayerStatsCache::where('params_hash', $paramsHash)->first();

        if ($dbCache && (! $latestReplayID || $dbCache->latest_replayID >= $latestReplayID)) {
            $payload = $dbCache->data;
        } else {
            $profile = $this->calculateProfile($blizz_id, $region, $game_type, $seasons, null, $startDate, $endDate, false);

            if (! $profile) {
                return null;
            }

            $payload = json_encode($profile->getAttributes());

            PlayerStatsCache::updateOrCreate(
                ['params_hash' => $paramsHash],
                [
                    'blizz_id' => $blizz_id,
                    'region' => $region,
                    'latest_replayID' => $latestReplayID ?? 0,
                    'data' => $payload,
                ]
            );
        }

        // Round trip so matches/hero_data are plain arrays, the same shape a profile_page row gives formatCache
        $profile = (new ProfilePage)->forceFill(json_decode($payload, true));

        if ($this->refreshPendingRatings($profile, $blizz_id)) {
            PlayerStatsCache::where('params_hash', $paramsHash)
                ->update(['data' => json_encode($profile->getAttributes())]);
        }

        return $profile;
    }

    private function calculateProfile($blizz_id, $region, $game_type, $season, $cachedData = null, $startDate = null, $endDate = null, $persist = true)
    {
        $games = function () use ($blizz_id, $region, $game_type, $season, $startDate, $endDate, $cachedData) {
            return DB::table('replay')
                ->join('player', 'player.replayID', '=', 'replay.replayID')
                ->join('scores', function ($join) {
                    $join->on('scores.replayID', '=', 'replay.replayID')
                        ->on('scores.battletag', '=', 'player.battletag');
                })
                ->join('heroes', 'heroes.id', '=', 'player.hero')
                ->where('blizz_id', $blizz_id)
                ->where('region', $region)
                ->where(function ($query) use ($game_type) {
                    if (is_null($game_type)) {
                        $query->whereNot('game_type', 0);
                    } else {
                        $query->whereIn('game_type', (array) $game_type);
                    }
                })
                ->tap(function ($query) use ($season, $startDate, $endDate) {
                    $this->globalDataService->applySeasonsOrDateRange($query, $season, $startDate, $endDate);
                })
                ->when($cachedData, function ($query, $cachedData) {
                    return $query->where('replay.replayID', '>', $cachedData->latest_replayID);
                });
        };

        // Talents are only shown for the latest matches, but a game without a talents
        // row has never been counted, so the totals keep that rule.
        $counted = fn () => $games()->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('talents')
                ->whereColumn('talents.replayID', 'replay.replayID')
                ->whereColumn('talents.battletag', 'player.battletag');
        });

        $counters = $this->profileCounters();

        $totals = $counted()
            ->selectRaw(collect($counters)
                ->map(fn ($expression, $column) => "COALESCE(SUM({$expression}), 0) AS {$column}")
                ->push('COUNT(*) AS games', 'MAX(replay.replayID) AS latest_replayID')
                ->implode(', '))
            ->first();

        if ((int) $totals->games === 0) {
            return $cachedData;
        }

        $account_level = Battletag::where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->max('account_level');

        $latest_replayID = (int) $totals->latest_replayID;

        $matches = $games()
            ->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            })
            ->select([
                'replay.replayID AS replayID',
                'replay.game_type AS game_type',
                'replay.game_date as game_date',
                'replay.game_map AS game_map',
                'player.winner AS winner',
                'player.hero AS hero',
                'player.player_conservative_rating AS player_conservative_rating',
                'player.player_change AS player_change',
                'player.hero_conservative_rating AS hero_conservative_rating',
                'player.hero_change AS hero_change',
                'player.role_conservative_rating AS role_conservative_rating',
                'player.role_change AS role_change',
                'talents.level_one AS level_one',
                'talents.level_four AS level_four',
                'talents.level_seven AS level_seven',
                'talents.level_ten AS level_ten',
                'talents.level_thirteen AS level_thirteen',
                'talents.level_sixteen AS level_sixteen',
                'talents.level_twenty AS level_twenty',
            ])
            ->orderByDesc('replay.game_date')
            ->limit(5)
            ->get();

        if ($cachedData) {
            $existingMatches = collect(json_decode($cachedData->matches, true));
            $mergedMatches = $existingMatches->merge($matches);
            $matches = $mergedMatches->sortByDesc('game_date')->take(5);
        }

        $matches = $matches->values();

        $heroData = $this->winLossBy($counted(), 'player.hero');

        if ($cachedData) {
            $existingHeroData = json_decode($cachedData->hero_data, true);
            foreach ($heroData as $hero => $data) {
                if (isset($existingHeroData[$hero])) {
                    $existingHeroData[$hero]['wins'] += $data['wins'];
                    $existingHeroData[$hero]['losses'] += $data['losses'];
                    $existingHeroData[$hero]['games_played'] += $data['games_played'];
                    $existingHeroData[$hero]['game_date'] = max($existingHeroData[$hero]['game_date'], $data['game_date']);
                } else {
                    $existingHeroData[$hero] = $data;
                }
            }
            $heroData = $existingHeroData;
        }

        $mapData = $this->winLossBy($counted(), 'replay.game_map');

        if ($cachedData) {
            $existingMapData = json_decode($cachedData->map_data, true);
            foreach ($mapData as $map => $data) {
                if (isset($existingMapData[$map])) {
                    $existingMapData[$map]['wins'] += $data['wins'];
                    $existingMapData[$map]['losses'] += $data['losses'];
                    $existingMapData[$map]['games_played'] += $data['games_played'];
                    $existingMapData[$map]['game_date'] = max($existingMapData[$map]['game_date'], $data['game_date']);
                } else {
                    $existingMapData[$map] = $data;
                }
            }
            $mapData = $existingMapData;
        }

        // Bucket by UTC hour-of-week (0–167): (dayOfWeek-1)*24 + hourOfDay
        // The frontend shifts these buckets by the user's local timezone offset to get local days.
        $weekdayData = $counted()
            ->selectRaw('WEEKDAY(replay.game_date) * 24 + HOUR(replay.game_date) AS bucket')
            ->selectRaw('SUM(player.winner = 1) AS wins, SUM(player.winner = 0) AS losses')
            ->groupBy('bucket')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->bucket => [
                'wins' => (int) $row->wins,
                'losses' => (int) $row->losses,
            ]])
            ->toArray();

        if ($cachedData && $cachedData->weekday_data) {
            $existingWeekdayData = json_decode($cachedData->weekday_data, true);
            foreach ($weekdayData as $bucket => $data) {
                if (isset($existingWeekdayData[$bucket])) {
                    $existingWeekdayData[$bucket]['wins'] += $data['wins'];
                    $existingWeekdayData[$bucket]['losses'] += $data['losses'];
                } else {
                    $existingWeekdayData[$bucket] = $data;
                }
            }
            $weekdayData = $existingWeekdayData;
        }

        if (! $cachedData) {
            $dataToSave = new ProfilePage;
            $dataToSave->blizz_id = $blizz_id;
            $dataToSave->region = $region;
            $dataToSave->game_type = $game_type;
            $dataToSave->season = $season;

            if ($persist) {
                $dataToSave->save();
            }
        } else {
            $dataToSave = $cachedData;
        }

        foreach (array_keys($counters) as $column) {
            $dataToSave->{$column} += (int) $totals->{$column};
        }

        $dataToSave->account_level = $account_level;
        $dataToSave->latest_replayID = $latest_replayID;

        $dataToSave->matches = $matches;
        $dataToSave->hero_data = $heroData;
        $dataToSave->map_data = $mapData;
        $dataToSave->weekday_data = json_encode($weekdayData);

        if ($persist) {
            $dataToSave->save();
        }

        return $dataToSave;
    }

    /**
     * Column => the per-game condition or value it sums.
     *
     * @return array<string, string>
     */
    private function profileCounters(): array
    {
        $counters = [
            'wins' => 'player.winner = 1',
            'losses' => 'player.winner = 0',
            'kills' => 'scores.kills',
            'deaths' => 'scores.deaths',
            'takedowns' => 'scores.takedowns',
            'first_to_ten_wins' => 'player.winner = 1 AND scores.first_to_ten = 1',
            'first_to_ten_losses' => 'player.winner = 0 AND scores.first_to_ten = 1',
            'second_to_ten_wins' => 'player.winner = 1 AND scores.first_to_ten = 0',
            'second_to_ten_losses' => 'player.winner = 0 AND scores.first_to_ten = 0',
            // 10355545 is the replayID where we started tracking mvp
            'mvp_games' => 'replay.replayID > 10355545',
            'games_mvp' => 'scores.match_award = 1',
            'time_on_fire_games' => 'scores.time_on_fire IS NOT NULL',
            'time_on_fire_total' => 'scores.time_on_fire',
            'total_time_played' => 'replay.game_length',
        ];

        $roles = [
            'bruiser' => 'Bruiser',
            'support' => 'Support',
            'ranged_assassin' => 'Ranged Assassin',
            'melee_assassin' => 'Melee Assassin',
            'healer' => 'Healer',
            'tank' => 'Tank',
        ];

        foreach ($roles as $key => $role) {
            $counters["{$key}_wins"] = "player.winner = 1 AND heroes.new_role = '{$role}'";
            $counters["{$key}_losses"] = "player.winner = 0 AND heroes.new_role = '{$role}'";
        }

        // Solo queue is stored as 0 or 1.
        $stacks = [
            'one' => "IN ('0', '1')",
            'two' => '= 2',
            'three' => '= 3',
            'four' => '= 4',
            'five' => '= 5',
        ];

        foreach ($stacks as $key => $condition) {
            $counters["stack_{$key}_wins"] = "player.winner = 1 AND player.stack_size {$condition}";
            $counters["stack_{$key}_losses"] = "player.winner = 0 AND player.stack_size {$condition}";
        }

        return $counters;
    }

    private function winLossBy($query, string $column)
    {
        return $query
            ->selectRaw("{$column} AS grouping_key")
            ->selectRaw('SUM(player.winner = 1) AS wins, SUM(player.winner = 0) AS losses, MAX(replay.game_date) AS game_date')
            ->groupBy($column)
            ->get()
            ->mapWithKeys(fn ($row) => [$row->grouping_key => [
                'wins' => (int) $row->wins,
                'losses' => (int) $row->losses,
                'games_played' => (int) $row->wins + (int) $row->losses,
                'game_date' => $row->game_date,
            ]]);
    }

    private function formatCache($data, $blizz_id, $region, $battletag)
    {

        $returnData = new \stdClass;
        $returnData->wins = $data->wins;
        $returnData->losses = $data->losses;
        $returnData->first_to_ten_wins = $data->first_to_ten_wins;
        $returnData->first_to_ten_losses = $data->first_to_ten_losses;
        $returnData->first_to_ten_win_rate = ($data->first_to_ten_wins + $data->first_to_ten_losses) > 0 ? round(($data->first_to_ten_wins / ($data->first_to_ten_wins + $data->first_to_ten_losses)) * 100, 2) : 0;
        $returnData->second_to_ten_wins = $data->second_to_ten_wins;
        $returnData->second_to_ten_losses = $data->second_to_ten_losses;
        $returnData->second_to_ten_win_rate = ($data->second_to_ten_wins + $data->second_to_ten_losses) > 0 ? round(($data->second_to_ten_wins / ($data->second_to_ten_wins + $data->second_to_ten_losses)) * 100, 2) : 0;
        $returnData->kdr = $data->deaths > 0 ? round($data->kills / $data->deaths, 2) : $data->kills;
        $returnData->kda = $data->deaths > 0 ? round($data->takedowns / $data->deaths, 2) : $data->takedowns;
        $returnData->account_level = $data->account_level;
        $returnData->win_rate = ($data->wins + $data->losses) > 0 ? round(($data->wins / ($data->wins + $data->losses)) * 100, 2) : 0;
        $returnData->bruiser_win_rate = ($data->bruiser_wins + $data->bruiser_losses) > 0 ? round(($data->bruiser_wins / ($data->bruiser_wins + $data->bruiser_losses)) * 100, 2) : 0;
        $returnData->support_win_rate = ($data->support_wins + $data->support_losses) > 0 ? round(($data->support_wins / ($data->support_wins + $data->support_losses)) * 100, 2) : 0;
        $returnData->ranged_assassin_win_rate = ($data->ranged_assassin_wins + $data->ranged_assassin_losses) > 0 ? round(($data->ranged_assassin_wins / ($data->ranged_assassin_wins + $data->ranged_assassin_losses)) * 100, 2) : 0;
        $returnData->melee_assassin_win_rate = ($data->melee_assassin_wins + $data->melee_assassin_losses) > 0 ? round(($data->melee_assassin_wins / ($data->melee_assassin_wins + $data->melee_assassin_losses)) * 100, 2) : 0;
        $returnData->healer_win_rate = ($data->healer_wins + $data->healer_losses) > 0 ? round(($data->healer_wins / ($data->healer_wins + $data->healer_losses)) * 100, 2) : 0;
        $returnData->tank_win_rate = ($data->tank_wins + $data->tank_losses) > 0 ? round(($data->tank_wins / ($data->tank_wins + $data->tank_losses)) * 100, 2) : 0;
        $returnData->mvp_rate = $data->mvp_games > 0 ? round(($data->games_mvp / $data->mvp_games) * 100, 2) : 0;

        $totalSeconds = $data->total_time_played;
        $days = floor($totalSeconds / (3600 * 24));
        $hours = floor(($totalSeconds % (3600 * 24)) / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $returnData->total_time_played = "{$days} days, {$hours} hours, {$minutes} minutes, {$seconds} seconds";

        if ($data->time_on_fire_games > 0) {
            $averageTimeOnFireInSeconds = $data->time_on_fire_total / $data->time_on_fire_games;

            $minutes = floor($averageTimeOnFireInSeconds / 60);
            $seconds = $averageTimeOnFireInSeconds % 60;

            $returnData->average_time_on_fire = "{$minutes} minutes, {$seconds} seconds";
        } else {
            $returnData->average_time_on_fire = '0 minutes, 0 seconds';
        }

        $hero_data = null;

        if (is_string($data->hero_data)) {
            $hero_data = collect(json_decode($data->hero_data, true));
        } else {
            $hero_data = collect($data->hero_data);
        }

        $top_three_win_rate_heroes = null;
        $gamePlayedThresholds = [20, 15, 10, 5, 0];

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = $this->globalDataService->getAllMapsKeyed();

        $talentData = $this->globalDataService->getAllTalentsKeyed();

        foreach ($gamePlayedThresholds as $threshold) {
            $filtered_hero_data = $hero_data->filter(function ($item) use ($threshold) {
                return $item['games_played'] >= $threshold;
            });

            $top_three_win_rate_heroes = $filtered_hero_data->map(function ($item, $key) use ($heroData, $blizz_id, $region, $battletag) {
                if ($item['games_played'] > 0) {
                    $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
                } else {
                    $item['win_rate'] = 0;
                }
                $item['hero_id'] = $key;
                $item['hero'] = $heroData[$key];
                $item['blizz_id'] = $blizz_id;
                $item['region'] = $region;
                $item['battletag'] = $battletag;
                $item['hovertext'] = $item['win_rate'].'% win rate over '.$item['games_played'].' games';

                return $item;
            })->sortByDesc('win_rate')->take(3)->values()->all();

            if (count($top_three_win_rate_heroes) >= 3) {
                break;
            }
        }

        $returnData->heroes_three_highest_win_rate = $top_three_win_rate_heroes;

        $top_three_most_played_heroes = $hero_data->map(function ($item, $key) use ($heroData, $blizz_id, $region, $battletag) {
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['hero_id'] = $key;
            $item['hero'] = $heroData[$key];
            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'].'% win rate over '.$item['games_played'].' games';

            return $item;
        })->sortByDesc('games_played')->take(3)->values()->all();

        $returnData->heroes_three_most_played = $top_three_most_played_heroes;

        $top_three_latest_played_heroes = $hero_data->map(function ($item, $key) use ($heroData, $blizz_id, $region, $battletag) {
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['hero_id'] = $key;
            $item['hero'] = $heroData[$key];
            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'].'% win rate over '.$item['games_played'].' games';

            return $item;
        })->sortByDesc('game_date')->take(3)->values()->all();

        $returnData->heroes_three_latest_played = $top_three_latest_played_heroes;

        $qm_mmr_data = MasterMMRDataQM::select('conservative_rating', 'win', 'loss')->filterByType(10000)->filterByGametype(1)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if ($qm_mmr_data) {
            $qm_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(1, 10000), $qm_mmr_data->mmr);
        }

        $ud_mmr_data = MasterMMRDataUD::select('conservative_rating', 'win', 'loss')->filterByType(10000)->filterByGametype(2)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if ($ud_mmr_data) {
            $ud_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(2, 10000), $ud_mmr_data->mmr);
        }

        $hl_mmr_data = MasterMMRDataHL::select('conservative_rating', 'win', 'loss')->filterByType(10000)->filterByGametype(3)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if ($hl_mmr_data) {
            $hl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(3, 10000), $hl_mmr_data->mmr);
        }

        $tl_mmr_data = MasterMMRDataTL::select('conservative_rating', 'win', 'loss')->filterByType(10000)->filterByGametype(4)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if ($tl_mmr_data) {
            $tl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(4, 10000), $tl_mmr_data->mmr);
        }

        $sl_mmr_data = MasterMMRDataSL::select('conservative_rating', 'win', 'loss')->filterByType(10000)->filterByGametype(5)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if ($sl_mmr_data) {
            $sl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(5, 10000), $sl_mmr_data->mmr);
        }
        $ar_mmr_data = MasterMMRDataAR::select('conservative_rating', 'win', 'loss')->filterByType(10000)->filterByGametype(6)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if ($ar_mmr_data) {
            $ar_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(6, 10000), $ar_mmr_data->mmr);
        }

        $returnData->qm_mmr_data = $qm_mmr_data;
        $returnData->ud_mmr_data = $ud_mmr_data;
        $returnData->hl_mmr_data = $hl_mmr_data;
        $returnData->tl_mmr_data = $tl_mmr_data;
        $returnData->sl_mmr_data = $sl_mmr_data;
        $returnData->ar_mmr_data = $ar_mmr_data;

        $map_data = null;

        if (is_string($data->map_data)) {
            $map_data = collect(json_decode($data->map_data, true));
        } else {
            $map_data = collect($data->map_data);
        }

        $top_three_win_rate_maps = null;

        foreach ($gamePlayedThresholds as $threshold) {
            $filtered_map_data = $map_data->filter(function ($item) use ($threshold) {
                return $item['games_played'] >= $threshold;
            });

            $top_three_win_rate_maps = $filtered_map_data->map(function ($item, $key) use ($maps, $blizz_id, $region, $battletag) {
                if ($item['games_played'] > 0) {
                    $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
                } else {
                    $item['win_rate'] = 0;
                }
                $item['map_id'] = $key;
                $item['game_map'] = $maps[$key];

                $item['blizz_id'] = $blizz_id;
                $item['region'] = $region;
                $item['battletag'] = $battletag;
                $item['hovertext'] = $item['win_rate'].'% win rate over '.$item['games_played'].' games';

                return $item;
            })->sortByDesc('win_rate')->take(3)->values()->all();

            if (count($top_three_win_rate_maps) >= 3) {
                break;
            }
        }

        $returnData->maps_three_highest_win_rate = $top_three_win_rate_maps;

        $top_three_most_played_maps = $map_data->map(function ($item, $key) use ($maps, $blizz_id, $region, $battletag) {
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['map_id'] = $key;
            $item['game_map'] = $maps[$key];

            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'].'% win rate over '.$item['games_played'].' games';

            return $item;
        })->sortByDesc('games_played')->take(3)->values()->all();

        $returnData->maps_three_most_played = $top_three_most_played_maps;

        $top_three_latest_played_maps = $map_data->map(function ($item, $key) use ($maps, $blizz_id, $region, $battletag) {
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['map_id'] = $key;
            $item['game_map'] = $maps[$key];

            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'].'% win rate over '.$item['games_played'].' games';

            return $item;
        })->sortByDesc('game_date')->take(3)->values()->all();

        $returnData->maps_three_latest_played = $top_three_latest_played_maps;

        $returnData->stack_one_wins = $data->stack_one_wins;
        $returnData->stack_one_losses = $data->stack_one_losses;
        $returnData->stack_one_win_rate = ($data->stack_one_wins + $data->stack_one_losses) > 0 ? round(($data->stack_one_wins / ($data->stack_one_wins + $data->stack_one_losses)) * 100, 2) : 0;

        $returnData->stack_two_wins = $data->stack_two_wins;
        $returnData->stack_two_losses = $data->stack_two_losses;
        $returnData->stack_two_win_rate = ($data->stack_two_wins + $data->stack_two_losses) > 0 ? round(($data->stack_two_wins / ($data->stack_two_wins + $data->stack_two_losses)) * 100, 2) : 0;

        $returnData->stack_three_wins = $data->stack_three_wins;
        $returnData->stack_three_losses = $data->stack_three_losses;
        $returnData->stack_three_win_rate = ($data->stack_three_wins + $data->stack_three_losses) > 0 ? round(($data->stack_three_wins / ($data->stack_three_wins + $data->stack_three_losses)) * 100, 2) : 0;

        $returnData->stack_four_wins = $data->stack_four_wins;
        $returnData->stack_four_losses = $data->stack_four_losses;
        $returnData->stack_four_win_rate = ($data->stack_four_wins + $data->stack_four_losses) > 0 ? round(($data->stack_four_wins / ($data->stack_four_wins + $data->stack_four_losses)) * 100, 2) : 0;

        $returnData->stack_five_wins = $data->stack_five_wins;
        $returnData->stack_five_losses = $data->stack_five_losses;
        $returnData->stack_five_win_rate = ($data->stack_five_wins + $data->stack_five_losses) > 0 ? round(($data->stack_five_wins / ($data->stack_five_wins + $data->stack_five_losses)) * 100, 2) : 0;

        $matches = null;

        if (is_string($data->matches)) {
            $matches = json_decode($data->matches, true);
        } else {
            $matches = $data->matches;
        }

        $returnData->matchData = collect($matches)->sortByDesc('game_date')->map(function ($match) use ($maps, $heroData, $talentData) {
            $match['game_type'] = $this->globalDataService->getGameTypeIDtoString()[$match['game_type']];
            $match['game_map'] = $maps[$match['game_map']];
            $match['hero'] = $heroData[$match['hero']];

            if ($match['level_one']) {
                if ($match['level_one'] != 0) {
                    $match['level_one'] = $talentData->has($match['level_one']) ? $talentData[$match['level_one']] : null;
                }
            }

            if ($match['level_four']) {
                if ($match['level_four'] != 0) {
                    $match['level_four'] = $talentData->has($match['level_four']) ? $talentData[$match['level_four']] : null;
                }

            }

            if ($match['level_seven']) {
                if ($match['level_seven'] != 0) {
                    $match['level_seven'] = $talentData->has($match['level_seven']) ? $talentData[$match['level_seven']] : null;
                }

            }

            if ($match['level_ten']) {
                if ($match['level_ten'] != 0) {
                    $match['level_ten'] = $talentData->has($match['level_ten']) ? $talentData[$match['level_ten']] : null;
                }

            }

            if ($match['level_thirteen']) {
                if ($match['level_thirteen'] != 0) {
                    $match['level_thirteen'] = $talentData->has($match['level_thirteen']) ? $talentData[$match['level_thirteen']] : null;
                }

            }

            if ($match['level_sixteen']) {
                if ($match['level_sixteen'] != 0) {
                    $match['level_sixteen'] = $talentData->has($match['level_sixteen']) ? $talentData[$match['level_sixteen']] : null;
                }

            }

            if ($match['level_twenty']) {
                if ($match['level_twenty'] != 0) {
                    $match['level_twenty'] = $talentData->has($match['level_twenty']) ? $talentData[$match['level_twenty']] : null;
                }
            }

            foreach (['player', 'hero', 'role'] as $type) {
                $rating = $match["{$type}_conservative_rating"];
                $match["{$type}_conservative_rating"] = round($rating, 2);
                $match["{$type}_mmr"] = round(1800 + 40 * $rating);
                $match["{$type}_change"] = round($match["{$type}_change"], 2);
            }

            return $match;
        })->values();

        $returnData->weekday_data = $data->weekday_data ? json_decode($data->weekday_data, true) : null;

        return $returnData;
    }

    /**
     * Recent matches can be stored before their MMR is calculated, at 1800. Swaps in
     * the calculated ratings once they exist, so each is looked up until it lands
     * rather than on every load forever. Returns whether anything changed.
     */
    private function refreshPendingRatings($data, $blizz_id): bool
    {
        $matches = json_decode(is_string($data->matches) ? $data->matches : json_encode($data->matches), true) ?? [];
        $changed = false;

        foreach ($matches as $key => $match) {
            if (round(1800 + 40 * $match['player_conservative_rating']) != 1800) {
                continue;
            }

            $updated = $this->getUpdatedMMRValues($match['replayID'], $blizz_id);

            if (! $updated || round(1800 + 40 * $updated->player_conservative_rating) == 1800) {
                continue;
            }

            foreach (['player', 'hero', 'role'] as $type) {
                $matches[$key]["{$type}_conservative_rating"] = $updated->{"{$type}_conservative_rating"};
                $matches[$key]["{$type}_change"] = $updated->{"{$type}_change"};
            }

            $changed = true;
        }

        if ($changed) {
            $data->matches = json_encode($matches);
        }

        return $changed;
    }

    private function getUpdatedMMRValues($replayID, $blizz_id)
    {
        return Player::select('player_conservative_rating', 'player_change', 'hero_conservative_rating', 'hero_change', 'role_conservative_rating', 'role_change')
            ->where('replayID', $replayID)
            ->where('blizz_id', $blizz_id)
            ->first();
    }
}
