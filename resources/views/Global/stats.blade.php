<?php


//$timeframe = array("major");
$timeframe = array("minor");
//$game_versions = array("2.49", "2.48");
$game_versions = array("2.49.2.77981");

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
$game_versions_minor = array('2.49.2.77981');
$game_type = array("5");
$region = array();
$game_map = array();
$hero_level = array();
$stat_type = array();
$player_league_tier = array();
$hero_league_tier = array();
$role_league_tier = array();
$mirror = array(0);


$page = "GlobalHeroStats";
$cache =  $page .
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

$return_data = Cache::remember($cache, calcluateCacheTime($timeframe, $game_versions), function () use ($game_versions_minor, $game_type, $region, $game_map,
                                      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
  $global_data = \GlobalHeroStatsData::instance($game_versions_minor, $game_type, $region, $game_map,
                                        $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
  $return_data = $global_data->getGlobalHeroStatData();
  return $return_data;
});

//print_r($return_data->toJson());
?>

<html>
<head>
    <title>Global Hero Stats</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.1/bootstrap-table.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.1/bootstrap-table.min.js"></script>
</head>
<body>
    <div class="container">
        <table id="table" data-height="460">
        <thead>
            <tr>
                <th data-field="hero">Hero</th>
                <th data-field="wins">Wins</th>
                <th data-field="losses">Losses</th>
                <th data-field="games_banned">Games Banned</th>
                <th data-field="games_played">Games Played</th>
                <th data-field="win_rate">Win Rate</th>
                <th data-field="pick_rate">Pick Rate</th>
                <th data-field="change">Change</th>
                <th data-field="popularity">Popularity</th>
                <th data-field="influence">Influence</th>
            </tr>
        </thead>
    </table>
    </div>
</body>
</html>

<script>
//https://stackoverflow.com/questions/32738763/laravel-csrf-token-mismatch-for-ajax-post-request
var $table = $('#table');
var mydata = <?php print_r($return_data->toJson());?>;
/*
var mydata = $.ajax({
    type: "POST",
    url: '/api/getGlobalHeroStatsData', // This is what I have updated
    data: {
      "_token": "{{ csrf_token() }}",
      id: 7
    }
}).done(function( msg ) {
    alert( msg );
});
*/


$(function () {
   $('#table').bootstrapTable({
       data: mydata
   });
});

</script>
