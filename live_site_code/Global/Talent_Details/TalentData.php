<?php

Class TalentData{

  private $hero;
  private $league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_type;
  private $timeframe;
  private $game_map;
  private $filter_type;
  private $hero_level;
  private $timeframe_type;
  function __construct($hero, $league_tier, $hero_league_tier, $role_league_tier, $game_type, $game_map, $timeframe_type, $timeframe, $filter_type, $hero_level){
    $this->hero = $hero;
    $this->league_tier = $league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->game_type = $game_type;
    $this->timeframe = $timeframe;
    $this->game_map = $game_map;
    $this->filter_type = $filter_type;
    $this->hero_level = $hero_level;
    $this->timeframe_type = $timeframe_type;

  }

  public function getData(){
    $returnData = array();
    $returnData = $this->getWinLossData($returnData);
    return $returnData;


  }

  private function getWinLossData($returnData){
    $db = (new DBConnection())->getConnection();
    $talentData = getTalentInfo($_SESSION['heroes'][$this->hero]["short_name"]);

    $versions = array();
    if($this->timeframe_type == "major"){
      for($i = 0; $i < count($this->timeframe); $i++){
        $version_data = getGameVersions($this->timeframe[$i]);
        for($j = 0; $j < count($version_data); $j++){
          $versions[$i + $j] = $version_data[$j];
        }
      }
    }else{
      $versions = $this->timeframe;
    }

    $sql = "SELECT" .
    " hero," .
    " win_loss," .
    " talent,";
    if($this->filter_type != "win_rate"){
      $sql .= " SUM(" . $this->filter_type . ") as total_" . $this->filter_type . ",";
    }
    $sql .= " global_hero_talents_details.level," .
    " SUM(games_played) as games_played" .
    " FROM global_hero_talents_details" .
    " inner join heroes_data_talents on heroes_data_talents.talent_id = global_hero_talents_details.talent";

    $sql .= " where game_version in (";

    for($i = 0; $i < count($versions); $i++){
      if($i == 0){
        $sql .= "\"" . $versions[$i] . "\"";

      }else{
        $sql .= "," . "\"" . $versions[$i] . "\"";

      }
    }
    $sql .= ")";

    if($this->game_type != ""){
      $gameTypeString = "";

      for($k = 0; $k < count($this->game_type); $k++){
        if($k == 0){
          $gameTypeString .= $_SESSION["game_types"][$this->game_type[$k]]["type_id"];

        }else{
          $gameTypeString .= "," . $_SESSION["game_types"][$this->game_type[$k]]["type_id"];

        }
      }
      $sql .= " AND game_type IN (" .  $gameTypeString . ")";
    }

    if(!in_array("", $this->league_tier)){
      $leagueTierString = "";
      for($k = 0; $k < count($this->league_tier); $k++){
        if($k == 0){
          $leagueTierString .= $_SESSION["league_tiers"][$this->league_tier[$k]];

        }else{
          $leagueTierString .= "," . $_SESSION["league_tiers"][$this->league_tier[$k]];

        }
      }
      $sql .= " AND league_tier IN (" . $leagueTierString . ")";
    }

    if(!in_array("", $this->hero_league_tier)){
      $heroLeagueTierString = "";
      for($k = 0; $k < count($this->hero_league_tier); $k++){
        if($k == 0){
          $heroLeagueTierString .= $_SESSION["league_tiers"][$this->hero_league_tier[$k]];
        }else{
          $heroLeagueTierString .= "," . $_SESSION["league_tiers"][$this->hero_league_tier[$k]];
        }
      }
      $sql .= " AND hero_league_tier IN (" . $heroLeagueTierString . ")";
    }

    if(!in_array("", $this->role_league_tier)){
      $roleLeagueTierString = "";
      for($k = 0; $k < count($this->role_league_tier); $k++){
        if($k == 0){
          $roleLeagueTierString .= $_SESSION["league_tiers"][$this->role_league_tier[$k]];
        }else{
          $roleLeagueTierString .= "," . $_SESSION["league_tiers"][$this->role_league_tier[$k]];
        }
      }
      $sql .= " AND role_league_tier IN (" . $roleLeagueTierString . ")";
    }

    if(!in_array("", $this->game_map)){

      $gameMapString = "";

      for($k = 0; $k < count($this->game_map); $k++){
        if($k == 0){
          $gameMapString .= $_SESSION['maps_ids'][$this->game_map[$k]];

        }else{
          $gameMapString .= "," . $_SESSION['maps_ids'][$this->game_map[$k]];

        }
      }
      $sql .= " AND game_map IN (" . $gameMapString . ")";

    }

    if(!in_array("", $this->hero_level)){

      $heroLevelString = "";

      for($k = 0; $k < count($this->hero_level); $k++){


        if($k == 0){
          if($this->hero_level[$k] == "1-5"){
            $heroLevelString .= "1";
          }else if($this->hero_level[$k] == "5-10"){
            $heroLevelString .= "5";
          }else if($this->hero_level[$k] == "10-15"){
            $heroLevelString .= "10";
          }else if($this->hero_level[$k] == "15-20"){
            $heroLevelString .= "15";
          }else{
            $heroLevelString .= $this->hero_level[$k];
          }


        }else{
          if($this->hero_level[$k] == "1-5"){
            $heroLevelString .= ", 1";
          }else if($this->hero_level[$k] == "5-10"){
            $heroLevelString .= ", 5";
          }else if($this->hero_level[$k] == "10-15"){
            $heroLevelString .= ", 10";
          }else if($this->hero_level[$k] == "15-20"){
            $heroLevelString .= ", 15";
          }else{
            $heroLevelString .= "," . $this->hero_level[$k];
          }

        }
      }
      $sql .= " AND hero_level IN (" . $heroLevelString . ")";
    }

    $sql .= " AND hero = "  . $_SESSION['heroes'][$this->hero]["id"];
    $sql .= " AND mirror = 0";

    $sql .= " AND not talent = 0";
    $sql .= " group by hero, sort, level, win_loss, talent";
    $sql .= " ORDER by level, sort, talent, win_loss";


    //echo $sql;
    //echo "<br />\n";
    //echo "<br />\n";

    $level_one_array = array();
    $level_four_array = array();
    $level_seven_array = array();
    $level_ten_array = array();
    $level_thirteen_array = array();
    $level_sixteen_array = array();
    $level_twenty_array = array();
    $role = "";

    $prevTalent = "start";
    $prevLevel = "start";
    $data = array();
    $data["games_played"] = 1;
    $data["wins"] = 0;
    $data["losses"] = 0;
    $data["losses_" . $this->filter_type] = 0;
    $data["wins_" . $this->filter_type] = 0;

    $level_one_total = 0;
    $level_four_total = 0;
    $level_seven_total = 0;
    $level_ten_total = 0;
    $level_thirteen_total = 0;
    $level_sixteen_total = 0;
    $level_twenty_total = 0;

    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {

          if($prevTalent != "start" && $prevTalent != $row["talent"] && array_key_exists($_SESSION['talent_ids'][$prevTalent], $talentData[0])){
            $data["games_played"] = $data["wins"] + $data["losses"];
            $data["win_rate"] = ($data["wins"] / $data["games_played"]) * 100;


            $data["title"] = $talentData[0][$_SESSION['talent_ids'][$prevTalent]];
            $data["description"] = $talentData[1][$_SESSION['talent_ids'][$prevTalent]];
            $data["hotkey"] = $talentData[2][$_SESSION['talent_ids'][$prevTalent]];
            $data["icon"] = $talentData[3][$_SESSION['talent_ids'][$prevTalent]];
            if($this->filter_type != "win_rate"){
              $data[$this->filter_type] = ($data["wins_" . $this->filter_type] + $data["losses_" . $this->filter_type] ) / $data["games_played"];

            }

            if($prevLevel == 1){
              $level_one_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_one_total += $data["games_played"];
            }else if($prevLevel == 4){
              $level_four_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_four_total += $data["games_played"];

            }else if($prevLevel == 7){
              $level_seven_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_seven_total += $data["games_played"];

            }else if($prevLevel == 10){
              $level_ten_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_ten_total += $data["games_played"];

            }else if($prevLevel == 13){
              $level_thirteen_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_thirteen_total += $data["games_played"];

            }else if($prevLevel == 16){
              $level_sixteen_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_sixteen_total += $data["games_played"];

            }else if($prevLevel == 20){
              $level_twenty_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
              $level_twenty_total += $data["games_played"];

            }
            $data = array();
            $data["games_played"] = 1;
            $data["wins"] = 0;
            $data["losses"] = 0;
            $data["losses_" . $this->filter_type] = 0;
            $data["wins_" . $this->filter_type] = 0;

          }



          if($row["win_loss"] == 1){
            $data["wins"] = $row["games_played"];

            if($this->filter_type != "win_rate"){
              $data["wins_" . $this->filter_type] = $row["total_" . $this->filter_type];
            }
          }else{
            $data["losses"] = $row["games_played"];
            if($this->filter_type != "win_rate"){
              $data["losses_" . $this->filter_type] = $row["total_" . $this->filter_type];
            }
          }

          $prevTalent = $row["talent"];
          $prevLevel = $row["level"];


        }
    }


      $data["games_played"] = $data["wins"] + $data["losses"];
      $data["win_rate"] = ($data["wins"] / $data["games_played"]) * 100;



      $data["title"] = $talentData[0][$_SESSION['talent_ids'][$prevTalent]];
      $data["description"] = $talentData[1][$_SESSION['talent_ids'][$prevTalent]];
      $data["hotkey"] = $talentData[2][$_SESSION['talent_ids'][$prevTalent]];
      $data["icon"] = $talentData[3][$_SESSION['talent_ids'][$prevTalent]];
      if($this->filter_type != "win_rate"){
        $data[$this->filter_type] = ($data["wins_" . $this->filter_type] + $data["losses_" . $this->filter_type] ) / $data["games_played"];

      }
      if($prevLevel == 1){
        $level_one_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_one_total += $data["games_played"];
      }else if($prevLevel == 4){
        $level_four_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_four_total += $data["games_played"];

      }else if($prevLevel == 7){
        $level_seven_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_seven_total += $data["games_played"];

      }else if($prevLevel == 10){
        $level_ten_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_ten_total += $data["games_played"];

      }else if($prevLevel == 13){
        $level_thirteen_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_thirteen_total += $data["games_played"];

      }else if($prevLevel == 16){
        $level_sixteen_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_sixteen_total += $data["games_played"];

      }else if($prevLevel == 20){
        $level_twenty_array[$_SESSION['talent_ids'][$prevTalent]] = $data;
        $level_twenty_total += $data["games_played"];

      }


      $data = array();
      $data["games_played"] = 1;
      $data["wins"] = 0;
      $data["losses"] = 0;


      foreach ($level_one_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_one_array[$title]["popularity"] = $talent_datas["games_played"] / $level_one_total * 100;
      }

      foreach ($level_four_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_four_array[$title]["popularity"] = $talent_datas["games_played"] / $level_four_total * 100;
      }

      foreach ($level_seven_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_seven_array[$title]["popularity"] = $talent_datas["games_played"] / $level_seven_total * 100;
      }

      foreach ($level_ten_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_ten_array[$title]["popularity"] = $talent_datas["games_played"] / $level_ten_total * 100;
      }

      foreach ($level_thirteen_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_thirteen_array[$title]["popularity"] = $talent_datas["games_played"] / $level_thirteen_total * 100;
      }

      foreach ($level_sixteen_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_sixteen_array[$title]["popularity"] = $talent_datas["games_played"] / $level_sixteen_total * 100;
      }

      foreach ($level_twenty_array as $title => $talent_datas){
        //print_r($level_one_array[$title]);
        $level_twenty_array[$title]["popularity"] = $talent_datas["games_played"] / $level_twenty_total * 100;
      }


      if($this->filter_type != "win_rate"){
        uasort($level_one_array, [$this, 'cmp']);
        uasort($level_four_array, [$this, 'cmp']);
        uasort($level_seven_array, [$this, 'cmp']);
        uasort($level_ten_array, [$this, 'cmp']);
        uasort($level_thirteen_array, [$this, 'cmp']);
        uasort($level_sixteen_array, [$this, 'cmp']);
        uasort($level_twenty_array, [$this, 'cmp']);
      }


      $returnData["1"] = $level_one_array;
      $returnData["4"] = $level_four_array;
      $returnData["7"] = $level_seven_array;
      $returnData["10"] = $level_ten_array;
      $returnData["13"] = $level_thirteen_array;
      $returnData["16"] = $level_sixteen_array;
      $returnData["20"] = $level_twenty_array;






    return $returnData;
  }

  function cmp( $a, $b ) {
    if($a[$this->filter_type] ==  $b[$this->filter_type] ){ return 0 ; }
    return ($a[$this->filter_type] > $b[$this->filter_type]) ? -1 : 1;
  }
}

?>
