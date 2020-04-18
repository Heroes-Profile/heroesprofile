<?php


//$timeframe = array("major");
$timeframe = array("minor");
//$game_versions = array("2.49", "2.48");
$game_versions = array("2.49.3.78256");

/*
//Needs to be calculated/pulled from session
$game_versions_minor = array('2.48.0.76437',
'2.48.1.76517',
'2.48.2.76753',
'2.48.2.76781',
'2.48.2.76893',
'2.48.3.77205',
'2.48.4.77406',
'2.49.0.77525',
'2.49.0.77548',
'2.49.1.77662',
'2.49.1.77692',
'2.49.2.77981',
'2.49.3.78256');
*/

$hero = 1;
$game_versions_minor = array('2.49.3.78256');
$game_type = array("5");
$region = array();
$game_map = array();
$hero_level = array();
$stat_type = array();
$player_league_tier = array();
$hero_league_tier = array();
$role_league_tier = array();
$mirror = array(0);

$page = "GlobalHeroStatsMatchups";
$cache =  $page .
          "|" . $hero .
          "|" . implode(",", $timeframe) .
          "|" . implode(",", $game_versions) .
          "|" . implode(",", $game_type) .
          "|" . implode(",", $region) .
          "|" . implode(",", $game_map) .
          "|" . implode(",", $hero_level) .
          "|" . implode(",", $stat_type) .
          "|"  . implode(",", $player_league_tier) .
          "|"  . implode(",", $hero_league_tier) .
          "|"  . implode(",", $role_league_tier);

$return_data = Cache::remember($cache, calcluateCacheTime($timeframe, $game_versions), function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
  $global_data = \GlobalHeroStatMatchupData::instance($hero, $game_versions_minor, $game_type, $region, $game_map,
                                        $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
  $return_data = $global_data->getGlobalHeroStatMapData();
  return $return_data;
});

print_r($return_data->toJson());
?>
