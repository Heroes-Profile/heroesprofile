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

    public function getHeroTalentData(Request $request){
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
            ->where("game_type", $gameType)
            ->where("region", $region)
            //->toSql();
            ->get();

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
            if(!array_key_exists($data["talent"]["level"], $formattedData)){
                $formattedData[$data["talent"]["level"]] = [];
            }

            $formattedData[$data["talent"]["level"]][$data["talent"]["sort"]]["wins"] = $data["wins"];
            $formattedData[$data["talent"]["level"]][$data["talent"]["sort"]]["losses"] = $data["losses"];
            $formattedData[$data["talent"]["level"]][$data["talent"]["sort"]]["games_played"] = $data["wins"] + $data["losses"];

            $formattedData[$data["talent"]["level"]][$data["talent"]["sort"]]["popularity"] = round((($data["losses"] + $data["wins"]) / $levelTotals[$data["talent"]["level"]]) * 100, 2);


            $formattedData[$data["talent"]["level"]][$data["talent"]["sort"]]["win_rate"] = ($data["wins"] + $data["losses"]) > 0 ? round(($data["wins"] / ($data["wins"] + $data["losses"])) * 100, 2): 0;
            $formattedData[$data["talent"]["level"]][$data["talent"]["sort"]]["talentInfo"] = $data["talent"];
        }



        return $formattedData;
    }

    public function getHeroTalentBuildData(Request $request){

    }
}
