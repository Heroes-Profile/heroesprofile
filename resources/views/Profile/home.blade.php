<?php
namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//Auth::loginUsingId(1, true);

/*
if (Auth::check()) {
    $user = Auth::user();
    print_r($user);
}
*/

$player_instance = \ProfileData::instance("Zemill#1940", 67280, 1, "", "");
$data = $player_instance->getPlayerProfileData();
?>

<?php
foreach ($data["role_data"] as $role => $role_data){
  if($role_data["wins"] + $role_data["losses"]){
    $data["role_data"][$role]["win_rate"] = ($role_data["wins"] / ($role_data["wins"] + $role_data["wins"])) * 100;
  }
}

//$data["role_data"] = \GlobalFunctions::instance()->sortKeyValueArray($data["role_data"], "win_rate_desc");

echo "Wins: " . $data["wins"]; echo "<br>";
echo "Losses: " . $data["losses"]; echo "<br>";

if(($data["first_to_ten_losses"] + $data["first_to_ten_wins"]) > 0){
  echo "First To Ten Win Rate: " . ($data["first_to_ten_wins"] / ($data["first_to_ten_wins"] + $data["first_to_ten_losses"])) * 100 ; echo "<br>";
}else{
  echo "First To Ten Win Rate: " . 0;
}

if(($data["second_to_ten_wins"] + $data["second_to_ten_losses"]) > 0){
  echo "Second To Ten Win Rate: " . ($data["second_to_ten_wins"] / ($data["second_to_ten_wins"] + $data["second_to_ten_losses"])) * 100 ; echo "<br>";
}else{
  echo "Second To Ten Win Rate: " . 0;
}

echo "KDR: " . $data["kills"] / $data["deaths"]; echo "<br>";
echo "KDA: " . $data["takedowns"] / $data["deaths"]; echo "<br>";

echo "Account Level: " . $data["account_level"]; echo "<br>";

echo "Kills: " . $data["kills"]; echo "<br>";
echo "Deaths: " . $data["deaths"]; echo "<br>";

echo "Win Rate: " . ($data["wins"] / ($data["wins"] + $data["losses"]) * 100); echo "<br>";

foreach ($data["role_data"] as $role => $role_data){
  if($role_data["wins"] + $role_data["losses"]){
    $data["role_data"][$role]["win_rate"] = ($role_data["wins"] / ($role_data["wins"] + $role_data["losses"])) * 100;
    echo $role . " Win Rate: " . $data["role_data"][$role]["win_rate"] . "<br>";
  }else{
    echo 0;
  }
}
echo "Total Time Played: " . $data["time_played"]; echo "<br>";


foreach ($data["hero_data"] as $hero => $hero_data){

  if($hero_data["wins"] + $hero_data["losses"]){
    $data["hero_data"][$hero]["win_rate"] = ($hero_data["wins"] / ($hero_data["wins"] + $hero_data["losses"])) * 100;
    //echo $hero . " Win Rate: " . $data["hero_data"][$hero]["win_rate"] . "<br>";
  }else{
    $data["hero_data"][$hero]["win_rate"] = 0;
  }

  $data["hero_data"][$hero]["games_played"] = $hero_data["wins"] + $hero_data["losses"];
}

$most_played = \GlobalFunctions::instance()->sortKeyValueArray($data["hero_data"], "games_played_desc");
//$most_played = array_slice($most_played, 0, 3);
echo "Most Played: ";
print_r(json_encode($most_played, true));
echo "<br>";

$temp_highest_win_rate = \GlobalFunctions::instance()->sortKeyValueArray($data["hero_data"], "win_rate_desc");

$highest_win_rate = array();
$counter = 0;
foreach ($temp_highest_win_rate as $hero => $hero_data){
  if($hero_data["games_played"] > 20){
    $highest_win_rate[$hero] = $hero_data;
    $counter++;
  }
  if($counter == 3){
    break;
  }
}

if($counter < 3){
  $counter = count($highest_win_rate) - 1;
  foreach ($temp_highest_win_rate as $hero => $hero_data){
    if($hero_data["games_played"] > 15){
      $highest_win_rate[$hero] = $hero_data;
      $counter++;
    }
    if($counter == 3){
      break;
    }
  }
}

if($counter < 3){
  $counter = count($highest_win_rate) - 1;
  foreach ($temp_highest_win_rate as $hero => $hero_data){
    if($hero_data["games_played"] > 10){
      $highest_win_rate[$hero] = $hero_data;
      $counter++;
    }
    if($counter >= 3){
      break;
    }
  }
}

if($counter < 3){
  $counter = count($highest_win_rate) - 1;
  foreach ($temp_highest_win_rate as $hero => $hero_data){
    if($hero_data["games_played"] > 5){
      $highest_win_rate[$hero] = $hero_data;
      $counter++;
    }
    if($counter >= 3){
      break;
    }
  }
}

if($counter < 3){
  $counter = count($highest_win_rate) - 1;
  foreach ($temp_highest_win_rate as $hero => $hero_data){
    $highest_win_rate[$hero] = $hero_data;
    $counter++;
    if($counter >= 3){
      break;
    }
  }
}
echo "Highest Win Rate: ";
print_r(json_encode($highest_win_rate, true));
echo "<br>";

$latest_played = \GlobalFunctions::instance()->sortKeyValueArray($data["hero_data"], "latest_game_desc");
$latest_played = array_slice($latest_played, 0, 3);

echo "Latest Played: ";
print_r(json_encode($latest_played, true));
echo "<br>";

echo "QM MMR: " . round(1800 + 40 *$data["game_type_data"][1]["mmr"]) . "<br>";// . " RANK: " . \GlobalFunctions::instance()->getRankName(round(1800 + 40 * $data["game_type_data"][1]["mmr"]), 1);
echo "UD MMR: " . round(1800 + 40 *$data["game_type_data"][2]["mmr"]) . "<br>";
echo "HL MMR: " . round(1800 + 40 *$data["game_type_data"][3]["mmr"]) . "<br>";
echo "TL MMR: " . round(1800 + 40 *$data["game_type_data"][4]["mmr"]) . "<br>";
echo "SL MMR: " . round(1800 + 40 *$data["game_type_data"][5]["mmr"]) . "<br>";
//Profile Page Done
 ?>
