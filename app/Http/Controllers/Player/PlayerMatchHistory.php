<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerMatchHistory extends Controller
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
            return [
                'data' => compact('battletag', 'blizz_id', 'region'),
                'status' => 'failure to validate inputs',
            ];
        }

        return view('Player.matchHistory')->with([
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
            //'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
        ]);
    }

    public function getData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['required', new GameTypeInputValidation()],
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
            'pagination_page' => 'required:integer',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
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
                'heroes.new_role as role',
                'talents.level_one AS level_one',
                'talents.level_four AS level_four',
                'talents.level_seven AS level_seven',
                'talents.level_ten AS level_ten',
                'talents.level_thirteen AS level_thirteen',
                'talents.level_sixteen AS level_sixteen',
                'talents.level_twenty AS level_twenty',
            ])
            ->where('blizz_id', $blizz_id)
            ->whereIn('game_type', $game_type)
            ->where('region', $region)
            ->when(! is_null($season), function ($query) use ($season) {
                $seasonDate = SeasonDate::find($season);
                if ($seasonDate) {
                    return $query->where('game_date', '>=', $seasonDate->start_date)
                        ->where('game_date', '<', $seasonDate->end_date);
                }

                return $query;
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
            ->orderByDesc('game_date')
            ->paginate($perPage, ['*'], 'page', $pagination_page);

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $modifiedResult = $result->map(function ($item) use ($heroData, $talentData, $maps) {
            $item->hero_id = $item->hero;
            $item->hero = $heroData[$item->hero];

            $item->game_type_id = $item->game_type;
            $item->game_type = $this->globalDataService->getGameTypeIDtoString()[$item->game_type];

            $item->game_map = $maps[$item->game_map]['name'];

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

            $item->winner = $item->winner == 1 ? 'True' : 'False';

            return $item;
        });

        return $result;
    }
}
