<?php

namespace App\Http\Controllers\Player;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Rules\SeasonInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;


class PlayerMatchupsController extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.matchupData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                ]);
    }

    public function getMatchupData(Request $request){
        //return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region', 'battletag']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'battletag' => 'required|string',
        ]);

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $battletag = $request['battletag'];

        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        $gameMap = (new GameMapInputValidation())->passes('map', $request["map"]);
        $hero = (new HeroInputByIDValidation())->passes('hero', $request["hero"]);
        $season = (new SeasonInputValidation())->passes('season', $request["season"]);

        $returnData = [];

        $heroData = $this->globalDataService->getHeroes();

        foreach ($heroData as $hero) {
            $returnData[$hero->id]['ally_wins'] = 0;
            $returnData[$hero->id]['ally_losses'] = 0;
            $returnData[$hero->id]['enemy_wins'] = 0;
            $returnData[$hero->id]['enemy_losses'] = 0;
        }
        $heroData = $heroData->keyBy('id');

        for($i = 0; $i <= 1; $i++){
            $subquery = DB::table('player')
                ->join('replay', 'replay.replayID', '=', 'player.replayID')
                ->where('blizz_id', $blizz_id)
                ->where('region', $region)
                ->where('team', $i)
                ->where('replay.game_type', '<>', 0)
                ->select('player.replayID');

            $result = DB::table('player')
                ->whereIn('replayID', $subquery)
                ->where('blizz_id', '<>', $blizz_id)
                ->groupBy('hero', 'team', 'winner')
                ->select('hero', 'team', 'winner', DB::raw('COUNT(*) AS total'))
                ->get();

            foreach($result as $hero => $value)
            {
                $returnData[$value->hero]["hero"] = $heroData[$value->hero];
                $returnData[$value->hero]["name"] = $heroData[$value->hero]["name"];
                $returnData[$value->hero]["battletag"] = $battletag;
                $returnData[$value->hero]["blizz_id"] = $blizz_id;
                $returnData[$value->hero]["region"] = $region;

                if($value->team == $i){
                    if($value->winner == 0){
                        $returnData[$value->hero]["ally_losses"] += $value->total;
                    }else{
                        $returnData[$value->hero]["ally_wins"] += $value->total;
                    }
                    $returnData[$value->hero]["ally_games_played"] = $returnData[$value->hero]["ally_wins"] + $returnData[$value->hero]["ally_losses"];
                    $returnData[$value->hero]["ally_win_rate"] = $returnData[$value->hero]["ally_games_played"] ? round(($returnData[$value->hero]["ally_wins"] / $returnData[$value->hero]["ally_games_played"]) * 100, 2): 0;
                }else{
                    if($value->winner == 0){
                        $returnData[$value->hero]["enemy_losses"] += $value->total;
                    }else{
                        $returnData[$value->hero]["enemy_wins"] += $value->total;
                    }
                    $returnData[$value->hero]["enemy_games_played"] = $returnData[$value->hero]["enemy_wins"] + $returnData[$value->hero]["enemy_losses"];
                    $returnData[$value->hero]["enemy_win_rate"] = $returnData[$value->hero]["enemy_games_played"] ? round(100 - ($returnData[$value->hero]["enemy_wins"] / $returnData[$value->hero]["enemy_games_played"]) * 100, 2): 0;
                }
            }
        }
        $topFiveAllyHeroes = collect($returnData)
            ->filter(function($value, $key) {
                return $value['ally_games_played'] >= 5;
            })
            ->sortByDesc('ally_win_rate')
            ->take(5)
            ->map(function($item) {
                $item["hovertext"] = "Won while on a team with " . $item["name"] . " " . $item["ally_win_rate"] . "% of the time";
                return $item;
            })
            ->values();

        $topFiveEnemyHeroes = collect($returnData)
            ->filter(function($value, $key) {
                return $value['enemy_games_played'] >= 5;
            })
            ->sortBy('enemy_win_rate')
            ->take(5)
            ->map(function($item) {
                $item["hovertext"] = "Lost against a team with " . $item["name"] . " " . (100 - $item["enemy_win_rate"]) . "% of games";
                return $item;
            })
            ->values();


        $returnData = array_values($returnData);

        usort($returnData, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return ["tabledata" => $returnData, "top_five_heroes" => $topFiveAllyHeroes , "top_five_enemies" => $topFiveEnemyHeroes];
    }

}
