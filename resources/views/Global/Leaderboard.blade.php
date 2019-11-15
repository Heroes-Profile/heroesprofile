<?php
namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//var_dump(openssl_get_cert_locations());
//die;
//$data = \PlayerData::instance()->getPlayerData();
//print_r(json_encode($data,true));


//Auth::loginUsingId(1, true);

/*
if (Auth::check()) {
    $user = Auth::user();
    print_r($user);
}
*/
/*
$data = \PlayerData::instance()->getPlayerData();
$data = json_encode($data,true);
print_r(json_encode($data));
*/
//$league_tiers = \GlobalFunctions::instance()->getLeagueTiers();
//print_r(json_encode($league_tiers, true));

  //$player_instance = \ProfileData::instance(67280, 1);
  //$data = $player_instance->getPlayerData();
    /*
  $player_stats = $player_instance->getPlayerSummaryStats("", 13);
  $player_hero_stats = $player_instance->getPlayerHeroMapSummary("", "", "hero");

  print_r(json_encode($player_instance->getMMRData(), true));

  $mmr_graph_data = $player_instance->graphMMRData();
  print_r(json_encode($mmr_graph_data, true));


  $player_map_stats = $player_instance->getPlayerHeroMapSummary("", "", "game_map");
  print_r(json_encode($player_map_stats, true));
  */

/*
  $latest_played = $player_instance->getLatestPlayed(5);
  print_r(json_encode($latest_played, true));
*/
//$player_instance->getAllReplaysFull();

$player_instance = \ProfileData::instance(67280, 1);
$data = $player_instance->getPlayerData();

//$friend_foe = $player_instance->getFriendAndFoeData("", "", "", "");

//$hero_all = $player_instance->getHeroAllData("", "", "", "", "");
$hero_mmr_all = $player_instance->getHeroAllMMRData("", "", "", "", "");


print_r(json_encode($hero_mmr_all, true));
 ?>
