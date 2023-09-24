<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\GameTypeInputValidation;
use Illuminate\Support\Facades\DB;

use App\Models\HeroesDataTalent;

class PlayerMatchHistory extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        $userinput = $this->globalDataService->getHeroModel($request["hero"]);

        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
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

    public function getData(Request $request){
        //return response()->json($request->all());

        $blizz_id = $request["blizz_id"];
        $region = $request["region"];
        $game_type = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        
        
        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                     ->on('scores.battletag', '=', 'player.battletag');
            })
            ->join('talents', function($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                     ->on('talents.battletag', '=', 'player.battletag');
            })
            ->join('heroes', 'heroes.id', '=', 'player.hero')
            ->select([
                "replay.replayID AS replayID",
                "replay.game_type AS game_type",
                "replay.game_date as game_date",
                "replay.game_map AS game_map",
                "player.winner AS winner",
                "player.hero AS hero",
                "talents.level_one AS level_one",
                "talents.level_four AS level_four",
                "talents.level_seven AS level_seven",
                "talents.level_ten AS level_ten",
                "talents.level_thirteen AS level_thirteen",
                "talents.level_sixteen AS level_sixteen",
                "talents.level_twenty AS level_twenty"
            ])
            ->where("blizz_id", $blizz_id)
            ->whereIn("game_type", $game_type)
            ->where("region", $region)
            ->orderByDesc("game_date")
            //->toSql();
            ->get();



        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $modifiedResult = $result->map(function ($item) use ($heroData, $talentData){
            $item->hero_id = $item->hero;
            $item->hero = $heroData[$item->hero];

            
            $item->game_type_id = $item->game_type;
            $item->game_type = $this->globalDataService->getGameTypeIDtoString()[$item->game_type];


            $item->level_one = $item->level_one && $talentData->has($item->level_one) ? $talentData[$item->level_one] : null;
            $item->level_four = $item->level_four && $talentData->has($item->level_four) ? $talentData[$item->level_four] : null;
            $item->level_seven = $item->level_seven && $talentData->has($item->level_seven) ? $talentData[$item->level_seven] : null;
            $item->level_ten = $item->level_ten && $talentData->has($item->level_ten) ? $talentData[$item->level_ten] : null;
            $item->level_thirteen = $item->level_thirteen && $talentData->has($item->level_thirteen) ? $talentData[$item->level_thirteen] : null;
            $item->level_sixteen = $item->level_sixteen && $talentData->has($item->level_sixteen) ? $talentData[$item->level_sixteen] : null;
            $item->level_twenty = $item->level_twenty && $talentData->has($item->level_twenty) ? $talentData[$item->level_twenty] : null;

            $item->winner = $item->winner == 1 ? "True" : "False";
            return $item;
        });

        return $modifiedResult;
    }
}
