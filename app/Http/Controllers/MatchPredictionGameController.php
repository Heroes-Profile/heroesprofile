<?php

namespace App\Http\Controllers;
use App\Models\GameType;
use App\Rules\GameTypeInputValidation;
use Illuminate\Support\Facades\Validator;
use App\Rules\UserAccountValidation;

use App\Models\Replay;
use App\Models\Player;
use App\Models\ReplayDraftOrder;
use App\Models\ReplayBan;
use App\Models\Map;
use App\Models\Talent;
use App\Models\MatchPredictionPlayerStat;
use App\Models\ReplayFingerprint;
use App\Models\HeroesDataTalent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
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

      $predicitionStats = null;

      $user = Auth::user();
      if($user){
        $predicitionStats = MatchPredictionPlayerStat::where('battlenet_accounts_id', $user->battlenet_accounts_id)
        ->where('season', 1)
        ->get();
      }
      return view('MatchPrediction.game')->with([
          'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
          'filters' => $this->globalDataService->getFilterData(),
          'gametypes' => $gametypes,
          'predictionstats' => $predicitionStats,
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
    
    $playerData = Player::select("battletag", "hero", "team", "player_conservative_rating")
      ->where("replayID", $replayID)
      ->get();

    $talents = Talent::select("battletag", "level_one", "level_four", "level_seven", "level_ten")
    ->where("replayID", $replayID)
    ->get();

    
    $talentData = HeroesDataTalent::all();
    $talentData = $talentData->keyBy('talent_id');

    $indexedTalents = $talents->mapWithKeys(function ($item) use ($talentData) {
      return [$item->battletag => [
          'level_one' => $talentData[$item->level_one],
          'level_four' => $talentData[$item->level_four],
          'level_seven' => $talentData[$item->level_seven],
          'level_ten' => $talentData[$item->level_ten],
      ]];
    });


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

    $averageMMR = [];

    $playerData = $playerData->map(function ($player) use ($heroData, &$averageMMR, $indexedTalents) {  
      $player->hero = $heroData[$player->hero];
      $averageMMR[] = 1800 + ($player->player_conservative_rating * 40);
      $player->talent = $indexedTalents[$player->battletag];

      unset($player->battletag);
      unset($player->player_conservative_rating);

      return $player;
    });

    sort($averageMMR);
    array_shift($averageMMR);
    array_pop($averageMMR);
    $averageValue = round(array_sum($averageMMR) / count($averageMMR));



    $rankTiers = $this->globalDataService->getRankTiers($gameType, 10000);
    $rankTier = $this->globalDataService->calculateSubTier($rankTiers, $averageValue);

    $rankTierName = str_replace(' ', '', strtolower(preg_replace('/\d/', '', $rankTier)));


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
      "firstPick" => $firstPick,
      "rank" => ucfirst($rankTierName),
    ];
  }

  public function chooseWinner(Request $request){


    $validationRules = [
      'team' => 'required|integer',
      'fingerprint' => 'required|string',
      'gametype' => ['required', new GameTypeInputValidation()],
      'practicemode' => 'required|boolean',
    ];

    if ($request->has('user') && !is_null($request->input('user'))) {
      $validationRules['user'] = ['sometimes', new UserAccountValidation()];
    } else {
      $validationRules['user'] = 'nullable'; 
    }


    $validator = Validator::make($request->all(), $validationRules);

    if ($validator->fails()) {
        return [
            'data' => $request->all(),
            'errors' => $validator->errors()->all(),
            'status' => 'failure to validate inputs',
        ];
    }
    $user = $request["user"];
    $game_type = GameType::where('short_name', $request['gametype'])->pluck('type_id')->first();


    $replayID = ReplayFingerprint::where("fingerprint", Crypt::decryptString($request["fingerprint"]))->value("replayID");


    $data = Player::select("winner")
      ->where("replayID", $replayID)
      ->where("team", $request["team"])
      ->first();


    if($user && !$request["practicemode"]){
      $existingRecord = MatchPredictionPlayerStat::where('battlenet_accounts_id', $user['battlenet_accounts_id'])
        ->where('season', 1)
        ->where('game_type', $game_type)
        ->first();

      if ($existingRecord) {
        if($data->winner == 1){
          $existingRecord->increment('win');
        }else{
          $existingRecord->increment('loss');
        }
      } else {
          MatchPredictionPlayerStat::insert([
            'battlenet_accounts_id' => $user['battlenet_accounts_id'],
            'season' => 1,
            'game_type' => $game_type,
            'win' => ($data->winner == 1 ? 1 : 0),
            'loss' => ($data->winner == 0 ? 1 : 0)
          ]);
      }
    }

    $predicitionStats = null;
    if($user){
      $predicitionStats = MatchPredictionPlayerStat::where('battlenet_accounts_id', $user["battlenet_accounts_id"])
      ->where('season', 1)
      ->get();
    }
    
    return ["replayID" => $replayID, "data" => $data->winner, "predictionstats" => $predicitionStats ];
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
