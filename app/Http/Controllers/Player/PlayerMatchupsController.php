<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\Map;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerMatchupsController extends Controller
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

        return view('Player.matchupData')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'gametypedefault' => ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'], //$this->globalDataService->getGameTypeDefault('multi'), //Removing user defined setting.  Doesnt make sense to me not to show ALL data for player profile pages to start

        ]);
    }

    public function getMatchupData(Request $request)
    {
      

        //return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region', 'battletag']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'battletag' => 'required|string',
        ]);

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $battletag = $request['battletag'];
        $game_type = $request['game_type'] ? GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray() : null;
        $season = $request['season'];
        $gameMap = $request['game_map'] ? Map::whereIn('name', $request['game_map'])->pluck('map_id')->toArray() : null;
        $inputhero = $request['hero'];

        $returnData = [];

        $heroData = $this->globalDataService->getHeroes();

        foreach ($heroData as $hero) {
            $returnData[$hero->id]['name'] = $hero->name;
            $returnData[$hero->id]['hero'] = $hero;
            $returnData[$hero->id]['ally_wins'] = 0;
            $returnData[$hero->id]['ally_losses'] = 0;
            $returnData[$hero->id]['ally_games_played'] = 0;
            $returnData[$hero->id]['enemy_wins'] = 0;
            $returnData[$hero->id]['enemy_losses'] = 0;
            $returnData[$hero->id]['enemy_games_played'] = 0;
        }
        $heroData = $heroData->keyBy('id');

        for ($i = 0; $i <= 1; $i++) {
            $subquery = DB::table('player')
                ->join('replay', 'replay.replayID', '=', 'player.replayID')
                ->where('blizz_id', $blizz_id)
                ->where('region', $region)
                ->where('team', $i)
                ->where(function ($query) use ($game_type) {
                    if (is_null($game_type)) {
                        $query->whereNot('game_type', 0);
                    } else {
                        $query->whereIn('game_type', $game_type);
                    }
                })
                ->when(! is_null($inputhero), function ($query) use ($inputhero) {
                    return $query->where('hero', $inputhero);
                })
                ->when(! is_null($gameMap), function ($query) use ($gameMap) {
                    return $query->whereIn('game_map', $gameMap);
                })
                ->select('player.replayID');

            $result = DB::table('player')
                ->whereIn('replayID', $subquery)
                ->where('blizz_id', '<>', $blizz_id)
                ->groupBy('hero', 'team', 'winner')
                ->select('hero', 'team', 'winner', DB::raw('COUNT(*) AS total'))
                //->toSql();
                ->get();

            foreach ($result as $hero => $value) {
                $returnData[$value->hero]['hero'] = $heroData[$value->hero];
                $returnData[$value->hero]['name'] = $heroData[$value->hero]['name'];
                $returnData[$value->hero]['battletag'] = $battletag;
                $returnData[$value->hero]['blizz_id'] = $blizz_id;
                $returnData[$value->hero]['region'] = $region;

                if ($value->team == $i) {
                    if ($value->winner == 0) {
                        $returnData[$value->hero]['ally_losses'] += $value->total;
                    } else {
                        $returnData[$value->hero]['ally_wins'] += $value->total;
                    }
                    $returnData[$value->hero]['ally_games_played'] = $returnData[$value->hero]['ally_wins'] + $returnData[$value->hero]['ally_losses'];
                    $returnData[$value->hero]['ally_win_rate'] = $returnData[$value->hero]['ally_games_played'] ? round(($returnData[$value->hero]['ally_wins'] / $returnData[$value->hero]['ally_games_played']) * 100, 2) : 0;
                } else {
                    if ($value->winner == 0) {
                        $returnData[$value->hero]['enemy_losses'] += $value->total;
                    } else {
                        $returnData[$value->hero]['enemy_wins'] += $value->total;
                    }
                    $returnData[$value->hero]['enemy_games_played'] = $returnData[$value->hero]['enemy_wins'] + $returnData[$value->hero]['enemy_losses'];
                    $returnData[$value->hero]['enemy_win_rate'] = $returnData[$value->hero]['enemy_games_played'] ? round(100 - ($returnData[$value->hero]['enemy_wins'] / $returnData[$value->hero]['enemy_games_played']) * 100, 2) : 0;
                }
            }
        }
        $topFiveAllyHeroes = collect($returnData)
            ->filter(function ($value, $key) {
                return $value['ally_games_played'] >= 5;
            })
            ->sortByDesc('ally_win_rate')
            ->take(5)
            ->map(function ($item) {
                $item['win_rate'] = $item['ally_win_rate'];
                $item['games_played'] = $item['ally_games_played'];

                return $item;
            })
            ->values();

        $topFiveEnemyHeroes = collect($returnData)
            ->filter(function ($value, $key) {
                return $value['enemy_games_played'] >= 5;
            })
            ->sortBy('enemy_win_rate')
            ->take(5)
            ->map(function ($item) {
                $item['win_rate'] = 100 - $item['enemy_win_rate'];
                $item['games_played'] = $item['enemy_games_played'];

                return $item;
            })
            ->values();

        $returnData = array_values($returnData);

        usort($returnData, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return ['tabledata' => $returnData, 'top_five_heroes' => $topFiveAllyHeroes, 'top_five_enemies' => $topFiveEnemyHeroes];
    }
}
