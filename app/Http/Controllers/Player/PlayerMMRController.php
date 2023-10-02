<?php

namespace App\Http\Controllers\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\GameTypeInputValidation;

use App\Models\LeagueBreakdown;
use App\Models\GameType;


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
            return [
                "data" => compact('battletag', 'blizz_id', 'region'),
                "status" => "failure to validate inputs"
            ];
        }

        return view('Player.mmrData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                ]);
    }

    public function getData(Request $request){
        //return response()->json($request->all());


        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'type' => 'required|in:Player,Hero,Role',
        ];

        $validator = Validator::make($request->all(), $validationRules);

       if ($validator->fails()) {
            return [
                "data" => $request->all(),
                "status" => "failure to validate inputs"
            ];
        }


        $blizz_id = $request["blizz_id"];
        $region = $request["region"];
        $game_type = GameType::where("short_name", $request["game_type"])->pluck("type_id")->first();

        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->select([
                "replay.replayID AS replayID",
                "replay.game_date as game_date",
                "replay.game_map AS game_map",
                "player.winner AS winner",
                "player.hero AS hero",
                "player.player_conservative_rating AS player_conservative_rating",
                "player.player_change AS player_change",
                "player.hero_conservative_rating AS hero_conservative_rating",
                "player.hero_change AS hero_change",
                "player.role_conservative_rating AS role_conservative_rating",
                "player.role_change AS role_change",
                "player.mmr_date_parsed as mmr_date_parsed",
            ])
            ->where("blizz_id", $blizz_id)
            ->where("game_type", $game_type)
            ->where("region", $region)
            //->toSql();
            ->orderByDesc("mmr_date_parsed")
            ->get();


        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $modifiedResult = $result->map(function ($item) use ($heroData){
            $item->hero_id = $item->hero;
            $item->hero = $heroData[$item->hero];

            $item->mmr = round(1800 + 40 * $item->player_conservative_rating);
            $item->mmr_change = round($item->player_change, 2);
            
            $item->winner = $item->winner == 1 ? "True" : "False";
            $item->x_label = $item->game_date;
            return $item;
        });

        $leagueBreakdown = LeagueBreakdown::where("type_role_hero", 10000)->where("game_type", $game_type)->get();

        foreach($leagueBreakdown as $data){
            $data->min_mmr = round($data->min_mmr);

            foreach($leagueBreakdown as $dataSecond){
                if(($dataSecond->league_tier - 1) == $data->league_tier){
                    $data->max_mmr = round($dataSecond->min_mmr);
                    break;
                }
            }
        }
        $fullBreakdownForTier = $this->globalDataService->getSubTiers($this->globalDataService->getRankTiers($game_type, 10000), $modifiedResult[0]->mmr);
        $rankTier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers($game_type, 10000), $modifiedResult[0]->mmr);

        $leagueBreakdownArray = $leagueBreakdown->toArray();
        $fullBreakdownForTierArray = $fullBreakdownForTier;

        $smallestMmr = min($fullBreakdownForTierArray);

        if($rankTier != "Master"){
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
                            'max_mmr' => $data['max_mmr'],
                            'tier' => $tier,
                            'mmr' => $mmr,
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





        return ["tableData" => $modifiedResult, "leagueData" => $extendedLeagueBreakdown];
    }
}
