<?php
namespace App;
use Session;




$type = "player";
$role = "";
$hero = "";
$page = "";
$region = "";
$season = Session::get("latest_season");
$game_type = Session::get("default_game_mode_id");


$leaderboardData = \LeaderboardData::instance($game_type, $season, $region, $type, $hero, $role, $page);
$data = $leaderboardData->getLeaderboardData();


print_r($data->toJson());

















?>
<!--
<section class="overall-match">
  <div class="matches-wrapper">
    <p class="section-callout mobile-show">Swipe left and right to view all table data</p>
    <div class="mobile-show left-right-arrows"><i class="fas fa-arrows-alt-h"></i></div>
<div class="table-wrapper">
<table id = "hero_chart" class="primary-data-table general-table">
  <thead>
    <th>Rank</th>
    <th>Battletag  </th>
    <th>Region</th>
    <th id="win_rate_column">Win Rate<i class="fas fa-sort-down"></i></th>
    <th id="adjusted_win_rate_column">Adjusted Win Rate<i class="fas fa-sort-down"></i></th>
    <th id="mmr_column"><?php echo ucfirst("Player") . " MMR"?><i class="fas fa-sort-down"></i></th>
    <th id="games_played_column">Games Played<i class="fas fa-sort-down"></i></th>
    <th >Win</th>
    <th>Loss</th>
</thead>

  <tbody>
    <?php
    $type = "player";
    $role = "role";
    $hero = "hero";
    $page = "";
    $region = "";
    $season = Session::get("latest_season");
    $game_type = Session::get("default_game_mode_id");

    $leaderboardData = \LeaderboardData::instance($game_type, $season, $region, $type, $hero, $role, $page);
    $data = $leaderboardData->getLeaderboardData();


    $counter = 1;

    foreach($data as $battletag => $playerdata){

    echo "<tr";
    echo"><td>" . $counter . "</td>" .
    "<td>" . "<a href=" . "\"" . "/Profile/?blizz_id=" . $playerdata['blizz_id'] . "&battletag=" . $playerdata['split_battletag'] . "&region=" . $playerdata['region'] . "\"" . "</a>" . $playerdata['split_battletag'] . "</td>" .

    "<td>" .  Session::get("regions_by_id")[$playerdata['region']] ."</td>" .

    "<td>" . number_format($playerdata['win_rate'],2) . "</td>" .
    "<td>" . number_format($playerdata["rating"], 2) . "</td>" .
    "<td>" . round((1800 + 40 * $playerdata["conservative_rating"])) . "</td>" .

    "<td>". $playerdata['games_played'] . "</td>" .
    "<td>" . $playerdata['win'] ."</td> " .
    "<td>" . $playerdata['loss'] ."</td>" .
    "</tr>";

    $counter++;

    }

    ?>
  </tbody>

</table>
</div>
<div class="loading-more"><div><i class="fas fa-spinner fa-spin"></i></div></div>
  <div class="main-link matches-view-more"><a href="javascript:void(0)" id="view-more-button">View More</a></a>
<a href="javascript:void(0)" class="back-to-top">Back to top</a>
        </div>


</div>

</section>
-->
