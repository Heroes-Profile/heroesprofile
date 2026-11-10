<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\Hero;
use App\Models\LeagueBreakdown;
use App\Models\MMRTypeID;
use App\Models\PlayerStatsCache;
use App\Rules\DateInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use App\Support\GlobalCacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerMMRController extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

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

        return view('Player.mmrData')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'gametypedefault' => $this->globalDataService->getMMRGameTypeDefault(),
            'urlparameters' => $request->query(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
        ]);
    }

    public function getData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'type' => 'required|in:Player,Hero,Role',
            'hero' => ['required_if:type,Hero', 'nullable', new HeroInputByIDValidation],
            'role' => ['required_if:type,Role', 'nullable', new RoleInputValidation],
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

        $game_type = GameType::where('short_name', $request['game_type'])->pluck('type_id')->first();
        $hero = $request['hero'] ? $this->globalDataService->getHeroesByID()[$request['hero']] : null;
        $type = $request['type'];
        $role = $request['role'];

        if ($type == 'Hero' && $hero == null) {
            return [
                'data' => $request->all(),
                'errors' => ['Hero is required when type is Hero.'],
                'status' => 'failure to validate inputs',
            ];
        }

        if ($type == 'Role' && is_null($role)) {
            return [
                'data' => $request->all(),
                'errors' => ['Role is required when type is Role.'],
                'status' => 'failure to validate inputs',
            ];
        }

        $rows = $this->ratedRows($request, $game_type, $hero, $role, $type);

        // No games, or every match still waiting on its rating: nothing to place.
        if ($rows->isEmpty()) {
            return;
        }
        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $modifiedResult = $rows->map(function ($item) use ($heroData) {
            $item->hero_id = $item->hero;
            $item->hero = $heroData[$item->hero];
            $item->mmr = round(1800 + 40 * $item->rating);
            $item->mmr_change = round($item->change, 2);
            $item->winner = $item->winner == 1 ? 'True' : 'False';
            $item->x_label = $item->game_date;
            unset($item->rating, $item->change);

            return $item;
        })->values();

        $mmrType = 0;
        if ($type == 'Player') {
            $mmrType = 10000;
        } elseif ($type == 'Hero') {
            $mmrTypeRecord = MMRTypeID::where('name', $hero->name)->first();
            if (is_null($mmrTypeRecord)) {
                return [
                    'data' => $request->all(),
                    'errors' => ['Unable to find MMR type for the selected hero.'],
                    'status' => 'failure to validate inputs',
                ];
            }
            $mmrType = $mmrTypeRecord->mmr_type_id;
        } elseif ($type == 'Role') {
            $mmrTypeRecord = MMRTypeID::where('name', $role)->first();
            if (is_null($mmrTypeRecord)) {
                return [
                    'data' => $request->all(),
                    'errors' => ['Unable to find MMR type for the selected role.'],
                    'status' => 'failure to validate inputs',
                ];
            }
            $mmrType = $mmrTypeRecord->mmr_type_id;
        }

        $leagueBreakdown = LeagueBreakdown::where('type_role_hero', $mmrType)->where('game_type', $game_type)->get();

        foreach ($leagueBreakdown as $data) {
            $data->min_mmr = round($data->min_mmr);

            foreach ($leagueBreakdown as $dataSecond) {
                if (($dataSecond->league_tier - 1) == $data->league_tier) {
                    $data->max_mmr = round($dataSecond->min_mmr);
                    break;
                }
            }
        }
        $rankTiers = $this->globalDataService->getRankTiers($game_type, $mmrType);
        $rankTier = $this->globalDataService->calculateSubTier($rankTiers, $modifiedResult[0]->mmr);

        $rankTierName = str_replace(' ', '', strtolower(preg_replace('/\d/', '', $rankTier)));

        $leagueBreakdownArray = $leagueBreakdown->toArray();

        if (isset($rankTierName) && isset($rankTiers[$rankTierName])) {
            $fullBreakdownForTierArray = $this->globalDataService->getSubTiers($rankTiers[$rankTierName], $rankTierName);
        } else {
            $fullBreakdownForTierArray = [];
        }

        $smallestMmr = 0;

        foreach ($rankTiers as $key => $data) {
            if ($rankTierName == $key) {
                $smallestMmr = $data['min_mmr'];
            }
        }

        if ($rankTier != 'Master') {
            foreach ($leagueBreakdownArray as $key => $data) {
                if ($data['min_mmr'] === $smallestMmr) {
                    unset($leagueBreakdownArray[$key]);
                    foreach ($fullBreakdownForTierArray as $tier => $mmr) {
                        $newData = [
                            'league_breakdowns_id' => $data['league_breakdowns_id'],
                            'type_role_hero' => $data['type_role_hero'],
                            'game_type' => $data['game_type'],
                            'league_tier' => $data['league_tier'],
                            'min_mmr' => $data['min_mmr'],
                            'max_mmr' => $mmr,
                            'tier' => $tier,
                            'tierFound' => $rankTier == $tier ? true : false,
                        ];
                        array_splice($leagueBreakdownArray, $key, 0, [$newData]);
                        $key++;
                        $data['min_mmr'] = $mmr;
                    }
                }
            }
        }

        $leagueBreakdownArray = array_values($leagueBreakdownArray);
        $extendedLeagueBreakdown = collect($leagueBreakdownArray);

        return ['tableData' => $modifiedResult, 'leagueData' => $extendedLeagueBreakdown];
    }

    // Rated games, newest first. A game's rating never changes once set, so the cache only adds games newer than its watermark.
    private function ratedRows(Request $request, $game_type, $hero, $role, string $type): Collection
    {
        if (! $this->globalDataService->usesProductionCaching()) {
            return $this->compactRows($this->rowsAfter($request, $game_type, $hero, $role, null)->where('mmr_ran', '!=', -1), $type);
        }

        $paramsHash = $this->paramsHash($request);
        $latestReplayId = $this->globalDataService->latestPlayerReplayId(
            $request['blizz_id'], $request['region'], $request['game_type'], $request['season'], $request['start_date'], $request['end_date']
        );

        $dbCache = PlayerStatsCache::where('params_hash', $paramsHash)->first();
        $rows = $dbCache ? collect(json_decode($dbCache->data)) : collect();
        $after = $dbCache ? (int) $dbCache->latest_replayID : null;

        if ($dbCache && (! $latestReplayId || $after >= $latestReplayId)) {
            return $rows;
        }

        $fetched = $this->rowsAfter($request, $game_type, $hero, $role, $after);
        $pending = $fetched->where('mmr_ran', -1);

        $rows = $this->compactRows($fetched->where('mmr_ran', '!=', -1), $type)->keyBy('replayID')
            ->union($rows->keyBy('replayID'))
            ->sortByDesc('mmr_date_parsed')
            ->values();

        // Games still waiting on a rating get fetched again next time
        $watermark = $pending->isNotEmpty()
            ? $pending->min('replayID') - 1
            : max((int) $latestReplayId, (int) $fetched->max('replayID'), (int) $after);

        PlayerStatsCache::updateOrCreate(
            ['params_hash' => $paramsHash],
            [
                'blizz_id' => $request['blizz_id'],
                'region' => $request['region'],
                'latest_replayID' => $watermark,
                'data' => json_encode($rows),
            ]
        );

        return $rows;
    }

    private function rowsAfter(Request $request, $game_type, $hero, $role, ?int $afterReplayId): Collection
    {
        return DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->select([
                'replay.replayID AS replayID',
                'replay.game_date as game_date',
                'replay.game_map AS game_map',
                'replay.mmr_ran AS mmr_ran',
                'player.winner AS winner',
                'player.hero AS hero',
                'player.player_conservative_rating AS player_conservative_rating',
                'player.player_change AS player_change',
                'player.hero_conservative_rating AS hero_conservative_rating',
                'player.hero_change AS hero_change',
                'player.role_conservative_rating AS role_conservative_rating',
                'player.role_change AS role_change',
                'player.mmr_date_parsed as mmr_date_parsed',
            ])
            ->where('blizz_id', $request['blizz_id'])
            ->where('game_type', $game_type)
            ->where('region', $request['region'])
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero->id);
            })
            ->when(! is_null($role), function ($query) use ($role) {
                return $query->whereIn('hero', Hero::select('id')->where('new_role', $role)->get()->toArray());
            })
            ->when($afterReplayId, fn ($query) => $query->where('replay.replayID', '>', $afterReplayId))
            ->tap(function ($query) use ($request) {
                $this->globalDataService->applySeasonsOrDateRange($query, $request['season'], $request['start_date'], $request['end_date']);
            })
            ->orderByDesc('mmr_date_parsed')
            ->get();
    }

    // Only the rating for this page's type is kept
    private function compactRows(Collection $rows, string $type): Collection
    {
        $column = ['Player' => 'player', 'Hero' => 'hero', 'Role' => 'role'][$type];

        return $rows->map(fn ($row) => (object) [
            'replayID' => $row->replayID,
            'game_date' => $row->game_date,
            'game_map' => $row->game_map,
            'winner' => $row->winner,
            'hero' => $row->hero,
            'mmr_date_parsed' => $row->mmr_date_parsed,
            'rating' => $row->{$column.'_conservative_rating'},
            'change' => $row->{$column.'_change'},
        ])->values();
    }

    private function paramsHash(Request $request): string
    {
        return hash('sha256', json_encode(GlobalCacheKey::normalize([
            'page' => 'mmr',
            'blizz_id' => $request['blizz_id'],
            'region' => $request['region'],
            'game_type' => $request['game_type'],
            'type' => $request['type'],
            'hero' => $request['hero'],
            'role' => $request['role'],
            'season' => $request['season'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
        ])));
    }
}
