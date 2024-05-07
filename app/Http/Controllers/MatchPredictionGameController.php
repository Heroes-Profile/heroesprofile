<?php

namespace App\Http\Controllers;
use App\Models\GameType;
use App\Rules\GameTypeInputValidation;
use Illuminate\Support\Facades\Validator;

use App\Models\Replay;
use App\Models\Player;
use App\Models\ReplayDraftOrder;
use App\Models\ReplayBan;
use App\Models\Map;
use App\Models\ReplayFingerprint;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Http\Request;

class MatchPredictionGameController extends Controller
{
  public function show(Request $request)
  {
      $gametypes = GameType::whereIn('type_id', [1, 5, 6])
      ->orderBy('type_id', 'ASC')
      ->get()
      ->map(function ($gameType) {
          return ['code' => $gameType->short_name, 'name' => $gameType->name];
      });

      return view('MatchPrediction.game')->with([
          'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
          'filters' => $this->globalDataService->getFilterData(),
          'gametypes' => $gametypes,
      ]);

  }

  public function getReplayData(Request $request){
    $validationRules = [
      'gametype' => ['required', new GameTypeInputValidation()],
    ];

    $validator = Validator::make($request->all(), $validationRules);

    if ($validator->fails()) {
        return [
            'data' => $request->all(),
            'errors' => $validator->errors()->all(),
            'status' => 'failure to validate inputs',
        ];
    }

    $gameType = GameType::where('short_name', $request["gametype"])->pluck('type_id')->first();

    $max = Replay::select("replayID")
      ->where("game_type", $gameType)
      ->where("game_version", $this->globalDataService->getDefaultTimeframe())
      ->max('replayID');

    $min = Replay::select("replayID")
      ->where("game_type", $gameType)
      ->where("game_version", $this->globalDataService->getDefaultTimeframe())
      ->min('replayID');


    $randomNumbers = [];
    for ($i = 0; $i < 1000; $i++) {
        $randomNumbers[] = rand($min, $max);
    }


    $replayData = Replay::select("replayID", "game_length", "game_map", "region")
      ->whereIn("replayID", $randomNumbers)
      ->where("game_type", $gameType)
      ->first();

    //$replayID = 51977960; 
    //$replayID = 51974603; 
    //$replayID = 51972542; 
    
    $replayID = $replayData->replayID;
    
    $playerData = Player::select("hero", "team")
      ->where("replayID", $replayID)
      ->get();

    $replayBans = ReplayBan::select("team", "hero")
      ->where("replayID", $replayID)
      ->get();

    $draftData = ReplayDraftOrder::select("pick_number", "type", "hero")
      ->where("replayID", $replayID)
      ->orderBy("pick_number", "ASC")
      ->get();
    
    if($replayBans && !$replayBans->isEmpty()){
      $banHeroToTeam = $replayBans->reduce(function ($carry, $item) {
        $carry[$item->hero] = $item->team;
        return $carry;
      }, []);
    }

    $heroToTeam = $playerData->reduce(function ($carry, $item) {
      $carry[$item->hero] = $item->team;
      return $carry;
    }, []);


    $maps = Map::all();
    $maps = $maps->keyBy('map_id');
    $regions = $this->globalDataService->getRegionIDtoString();

    $replayData->game_map = $maps[$replayData->game_map];

    $totalSeconds = $replayData->game_length - 70;
    $minutes = floor($totalSeconds / 60);
    $seconds = $totalSeconds % 60;
    $replayData->timeFormat = "$minutes minutes $seconds seconds";

    $replayData->regionString = $regions[$replayData->region];
    unset($replayData->replayID);

    $heroData = $this->globalDataService->getHeroes();
    $heroData = $heroData->keyBy('id');

    $playerData = $playerData->map(function ($player) use ($heroData) {  
      $player->hero = $heroData[$player->hero];

      return $player;
    });

    $groupedDraftData = null;
    if ($draftData && !$draftData->isEmpty()) {
      $draftData = $draftData->map(function ($draftSlot) use ($heroData, $banHeroToTeam, $heroToTeam) {  
          $draftSlot->team = $draftSlot->type == 0 ? $banHeroToTeam[$draftSlot->hero] : $heroToTeam[$draftSlot->hero]; 
          $draftSlot->hero = $draftSlot->hero != 0 ? $heroData[$draftSlot->hero] : null;
          return $draftSlot;
      });
  
      // Group the collection first by 'team', and then by 'type' within each 'team' group
      $groupedDraftData = $draftData->groupBy('team')->map(function ($teamGroup) {
          return $teamGroup->groupBy(function ($slot) {
              return $slot->type == 0 ? 'bans' : 'picks';
          });
      });
    }
  


    $groupedPlayerData = $playerData->groupBy('team');

    $firstPick = $this->determineFirstPick($replayBans, $draftData); 


    
    return [
      "fingerprint" => Crypt::encryptString(ReplayFingerprint::where("replayID", $replayID)->value("fingerprint")), 
      "replayData" => $replayData, 
      "playerData" => $groupedPlayerData,
      "draftData" => $groupedDraftData && !$groupedDraftData->isEmpty() ? $groupedDraftData : null, 
      "firstPick" => $firstPick
    ];
  }

  public function chooseWinner(Request $request){

    $validationRules = [
      'team' => 'required|integer',
      'fingerprint' => 'required|string',
    ];

    $validator = Validator::make($request->all(), $validationRules);

    if ($validator->fails()) {
        return [
            'data' => $request->all(),
            'errors' => $validator->errors()->all(),
            'status' => 'failure to validate inputs',
        ];
    }

    $replayID = ReplayFingerprint::where("fingerprint", Crypt::decryptString($request["fingerprint"]))->value("replayID");


    $data = Player::select("winner")
      ->where("replayID", $replayID)
      ->where("team", $request["team"])
      ->first();
      
    
    
    return ["replayID" => $replayID, "data" => $data->winner == 1 ? "You made the correct choice, congratulations" : "You made the incorrect choice"];
  }

  private function determineFirstPick($replayBans, $draftData){
    if(!$replayBans || $replayBans->isEmpty()){
      return null;
    }

    $firstTypeOneResult = $draftData->first(function ($item) {
      return $item->type == 1;
    });

    return $firstTypeOneResult;
  }

}
