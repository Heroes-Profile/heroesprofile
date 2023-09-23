<?php

namespace App\Http\Controllers\Player;
use App\Services\GlobalDataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Rules\HeroInputValidation;
use App\Rules\GameTypeInputValidation;


use App\Models\HeroesDataTalent;


class PlayerTalentsController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

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


        return view('Player.talentData')->with([
                'userinput' => $userinput,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                ]);
    }
    public function getPlayerTalentData(Request $request){
          //return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region', 'battletag']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'battletag' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }

        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);

        $battletag = $request["battletag"];
        $blizz_id = $request["blizz_id"];
        $region = $request["region"];
        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('talents', function($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                     ->on('talents.battletag', '=', 'player.battletag');
            })
            ->select([
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
            ->where("hero", $hero)
            ->whereIn("game_type", $gameType)
            ->where("region", $region)
            //->toSql();
            ->get();

        return [
            "talentData" => $this->getHeroTalentData($result),
            "buildData" => $this->getHeroTalentBuildData($result),
        ];
    }

    private function getHeroTalentData($result){
        $returnData = [];
        $levelKeys = ["level_one", "level_four", "level_seven", "level_ten", "level_thirteen", "level_sixteen", "level_twenty"];

        $resultCollection = collect($result);

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $resultCollection->each(function ($data) use (&$returnData, $levelKeys, $talentData) {
            foreach ($levelKeys as $levelKey) {
                if ($data->$levelKey == 0 || !$talentData->has($data->$levelKey)) {
                    continue;
                }

                $key = $data->$levelKey;

                if (!isset($returnData[$key])) {
                    $returnData[$key] = ["wins" => 0, "losses" => 0];
                }

                $returnData[$key][$data->winner == 1 ? "wins" : "losses"]++;
                $returnData[$key]["talent"] = $talentData[$data->$levelKey];
            }
        });

        $formattedData = [];

        $levelTotals = [];

        foreach($returnData as $data){
            if(!array_key_exists($data["talent"]["level"], $levelTotals)){
                $levelTotals[$data["talent"]["level"]] = 0;
            }
            $levelTotals[$data["talent"]["level"]] += $data["wins"] + $data["losses"];
        }

        foreach($returnData as $data){
            $level = $data["talent"]["level"];
            $sort = ($data["talent"]["sort"] - 1);

            if(!array_key_exists($level, $formattedData)){
                $formattedData[$level] = [];
            }

            if(!array_key_exists($sort, $formattedData[$level])){
                $formattedData[$level][$sort] = [];
            }



            $formattedData[$level][$sort] = [
                        'win_rate' =>($data["wins"] + $data["losses"]) > 0 ? round(($data["wins"] / ($data["wins"] + $data["losses"])) * 100, 2): 0,
                        'wins' => $data["wins"],
                        'losses' => $data["losses"],
                        'sort' => $data["talent"]["sort"],
                        'popularity' =>  round((($data["losses"] + $data["wins"]) / $levelTotals[$data["talent"]["level"]]) * 100, 2),
                        'games_played' =>  $data["wins"] + $data["losses"],
                        'talentInfo' => $data["talent"]
            ];

        }

   
        return $formattedData;
    }

    private function getHeroTalentBuildData($result){
        $returnData = [];

        foreach($result as $replay){
            $level_one = $replay->level_one;
            $level_four = $replay->level_four;
            $level_seven = $replay->level_seven;
            $level_ten = $replay->level_ten;
            $level_thirteen = $replay->level_thirteen;
            $level_sixteen = $replay->level_sixteen;
            $level_twenty = $replay->level_twenty;

            if($level_one == 0 || $level_four == 0 || $level_seven == 0 || $level_ten == 0 || $level_thirteen == 0 || $level_sixteen == 0 || $level_twenty == 0){
                continue;
            }
            $key = $level_one . "|" . $level_four . "|" . $level_seven . "|" . $level_ten . "|" . $level_thirteen . "|" . $level_sixteen . "|" . $level_twenty;
            if(!array_key_exists($key, $returnData)){
                $returnData[$key] = [];
                $returnData[$key]["wins"] = 0;
                $returnData[$key]["losses"] = 0;
                $returnData[$key]["games_played"] = 0;

                $returnData[$key]["level_one"] = $replay->level_one;
                $returnData[$key]["level_four"] = $replay->level_four;
                $returnData[$key]["level_seven"] = $replay->level_seven;
                $returnData[$key]["level_ten"] = $replay->level_ten;
                $returnData[$key]["level_thirteen"] = $replay->level_thirteen;
                $returnData[$key]["level_sixteen"] = $replay->level_sixteen;
                $returnData[$key]["level_twenty"] = $replay->level_twenty;
            }

            if($replay->winner == 1){
                $returnData[$key]["wins"]++;
            }else{
                $returnData[$key]["losses"]++;
            }
            $returnData[$key]["games_played"]++;

        }

        usort($returnData, function ($a, $b) {
            return $b['games_played'] - $a['games_played'];
        });

        $returnData = array_slice($returnData, 0, 85);

        foreach($returnData as &$data){
            foreach($result as $replay){
                $level_one = $replay->level_one;
                $level_four = $replay->level_four;
                $level_seven = $replay->level_seven;
                $level_ten = $replay->level_ten;
                $level_thirteen = $replay->level_thirteen;
                $level_sixteen = $replay->level_sixteen;
                $level_twenty = $replay->level_twenty;

                if($data["level_one"] != $level_one || $data["level_four"] != $level_four || $data["level_seven"] != $level_seven || $data["level_ten"] != $level_ten || $level_twenty != 0){
                    continue;
                }

                if($replay->winner == 1){
                    $data["wins"]++;

                }else{
                    $data["losses"]++;
                }
                $data["games_played"]++;
            }
        }

        return $returnData;
    }
}