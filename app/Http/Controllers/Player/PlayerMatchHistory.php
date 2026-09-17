<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Rules\DateInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerMatchHistory extends Controller
{
    /**
     * Every stat column on `scores`.
     *
     * The join was commented out here for a long time, so match history carried
     * no stat line at all. The public API's `players/matches` replaces the old
     * `/Player/Replays`, which did return these — without them a caller has to
     * fetch each match separately to get a stat it used to receive in bulk.
     *
     * The site's own match history does not render them yet.
     */
    private const SCORE_COLUMNS = [
        'level', 'kills', 'assists', 'takedowns', 'deaths', 'highest_kill_streak',
        'hero_damage', 'siege_damage', 'structure_damage', 'minion_damage',
        'creep_damage', 'summon_damage', 'time_cc_enemy_heroes', 'healing',
        'self_healing', 'damage_taken', 'experience_contribution', 'town_kills',
        'time_spent_dead', 'merc_camp_captures', 'watch_tower_captures',
        'meta_experience', 'match_award', 'protection_allies', 'silencing_enemies',
        'rooting_enemies', 'stunning_enemies', 'clutch_heals', 'escapes',
        'vengeance', 'outnumbered_deaths', 'teamfight_escapes', 'teamfight_healing',
        'teamfight_damage_taken', 'teamfight_hero_damage', 'multikill',
        'physical_damage', 'spell_damage', 'regen_globes', 'first_to_ten',
        'time_on_fire',
    ];

    /**
     * Qualified, because `level` and `match_award` are not unique across the
     * joined tables.
     *
     * @return array<int, string>
     */
    private static function scoreColumns(): array
    {
        return array_map(
            fn (string $column) => 'scores.'.$column.' AS '.$column,
            self::SCORE_COLUMNS
        );
    }

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

        $showcustomgames = $this->globalDataService->showcustomgames($battletag, $blizz_id, $region);
        $gametypedefault = $this->globalDataService->getPlayerGameTypeDefault();

        return view('Player.matchHistory')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),
            'playermatchtablestyle' => $this->globalDataService->getPlayerMatchStyle(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $gametypedefault,
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'showcustomgames' => $showcustomgames,
            'urlparameters' => $request->query(),
        ]);
    }

    public function showLatest(Request $request, $battletag, $blizz_id, $region)
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

        $latest_replay = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->select([
                'replay.replayID AS replayID',
            ])
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            // Custom game pages are for opted-in participants only.
            ->where('game_type', '<>', 0)
            ->orderByDesc('game_date')
            ->first();
        if ($latest_replay) {
            return \Redirect::to('/Match/Single/'.$latest_replay->replayID);
        } else {
            return;
        }
    }

    public function getData(Request $request)
    {

        // return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['required', new GameTypeInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation],
            'start_date' => ['sometimes', 'nullable', new DateInputValidation],
            'end_date' => ['sometimes', 'nullable', new DateInputValidation],
            'stack_size' => ['sometimes', 'nullable', 'string', 'in:All,Solo,Duo,3 Players,4 Players,5 Players'],
            'pagination_page' => 'required|integer|min:1',
            'ff_blizzid' => 'sometimes|nullable|integer',
            'ff_region' => 'sometimes|nullable|integer',
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

        $game_type = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();
        $role = $request['role'];
        $hero = $request['hero'];
        $game_map = $request['game_map'] ? Map::whereIn('name', $request['game_map'])->pluck('map_id')->toArray() : null;
        $season = $request['season'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $stack_size = match ($request['stack_size'] ?? null) {
            'Solo' => [0, 1],
            'Duo' => 2,
            '3 Players' => 3,
            '4 Players' => 4,
            '5 Players' => 5,
            default => null,
        };

        $ff_blizzid = $request['ff_blizzid'] ?? null;
        $ff_region = $request['ff_region'] ?? null;

        // Filtering by a second account reveals which games they were in, so they are
        // held to the same privacy rule as the player.
        if ($ff_blizzid && $ff_region
            && $this->globalDataService->isHiddenFrom($ff_blizzid, $ff_region, Auth::user())) {
            return response()->json(['status' => 'private'], 403);
        }

        $pagination_page = $request['pagination_page'];
        $perPage = 100;

        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            })
            ->join('heroes', 'heroes.id', '=', 'player.hero')
            ->select([
                'replay.replayID AS replayID',
                'replay.game_type AS game_type',
                'replay.game_date as game_date',
                'replay.game_map AS game_map',
                'player.winner AS winner',
                'player.hero AS hero',
                'player_conservative_rating',
                'player_change',
                'hero_conservative_rating',
                'hero_change',
                'role_conservative_rating',
                'role_change',
                'heroes.new_role as role',
                'talents.level_one AS level_one',
                'talents.level_four AS level_four',
                'talents.level_seven AS level_seven',
                'talents.level_ten AS level_ten',
                'talents.level_thirteen AS level_thirteen',
                'talents.level_sixteen AS level_sixteen',
                'talents.level_twenty AS level_twenty',
                ...self::scoreColumns(),
            ])
            ->where('blizz_id', $blizz_id)
            ->whereIn('game_type', $game_type)
            ->where('region', $region)
            ->tap(function ($query) use ($season, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season, $startDate, $endDate);
            })
            ->when(! is_null($game_map), function ($query) use ($game_map) {
                return $query->whereIn('game_map', $game_map);
            })
            ->when(! is_null($role), function ($query) use ($role) {
                return $query->where('new_role', $role);
            })
            ->when(! is_null($hero), function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when(! is_null($stack_size), function ($query) use ($stack_size) {
                return is_array($stack_size)
                    ? $query->whereIn('stack_size', $stack_size)
                    : $query->where('stack_size', $stack_size);
            })
            ->when($ff_blizzid && $ff_region, function ($query) use ($ff_blizzid, $ff_region) {
                return $query->where('replay.region', $ff_region)
                    ->whereExists(fn ($sub) => $sub->select(DB::raw(1))
                        ->from('player as ff')
                        ->whereColumn('ff.replayID', 'replay.replayID')
                        ->where('ff.blizz_id', $ff_blizzid));
            })
            ->orderByDesc('game_date')
            ->paginate($perPage, ['*'], 'page', $pagination_page);

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $talentData = HeroesDataTalent::withAllStatuses()->get();
        $talentData = $talentData->keyBy('talent_id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $modifiedResult = $result->map(function ($item) use ($heroData, $talentData, $maps) {
            $item->hero_id = $item->hero;
            $item->hero = $heroData[$item->hero];

            $item->game_type_id = $item->game_type;
            $item->game_type = $this->globalDataService->getGameTypeIDtoString()[$item->game_type];

            $item->game_map = $maps[$item->game_map];

            $item->player_mmr = round(1800 + (40 * $item->player_conservative_rating));
            $item->player_change = $item->player_change;
            $item->hero_mmr = round(1800 + (40 * $item->hero_conservative_rating));
            $item->hero_change = $item->hero_change;
            $item->role_mmr = round(1800 + (40 * $item->role_conservative_rating));
            $item->role_change = $item->role_change;

            if ($item->level_one) {
                if ($item->level_one != 0) {
                    $item->level_one = $talentData->has($item->level_one) ? $talentData[$item->level_one] : null;
                }
            }

            if ($item->level_four) {
                if ($item->level_four != 0) {
                    $item->level_four = $talentData->has($item->level_four) ? $talentData[$item->level_four] : null;
                }
            }

            if ($item->level_seven) {
                if ($item->level_seven != 0) {
                    $item->level_seven = $talentData->has($item->level_seven) ? $talentData[$item->level_seven] : null;
                }
            }

            if ($item->level_ten) {
                if ($item->level_ten != 0) {
                    $item->level_ten = $talentData->has($item->level_ten) ? $talentData[$item->level_ten] : null;
                }
            }

            if ($item->level_thirteen) {
                if ($item->level_thirteen != 0) {
                    $item->level_thirteen = $talentData->has($item->level_thirteen) ? $talentData[$item->level_thirteen] : null;
                }
            }

            if ($item->level_sixteen) {
                if ($item->level_sixteen != 0) {
                    $item->level_sixteen = $talentData->has($item->level_sixteen) ? $talentData[$item->level_sixteen] : null;
                }
            }

            if ($item->level_twenty) {
                if ($item->level_twenty != 0) {
                    $item->level_twenty = $talentData->has($item->level_twenty) ? $talentData[$item->level_twenty] : null;
                }
            }

            $item->winner = $item->winner;

            return $item;
        });

        return $result;
    }
}
