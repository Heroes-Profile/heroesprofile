<?php

Class TalentDataBuilds{

  private $hero;
  private $league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_type;
  private $timeframe;
  private $game_map;
  private $build_type;
  private $filter_type;
  private $hero_level;
  private $timeframe_type;
  private $build_max_level;

  function __construct($hero, $league_tier, $hero_league_tier, $role_league_tier, $game_type, $game_map, $timeframe_type, $timeframe, $build_type, $build_max_level, $filter_type, $hero_level){
    $this->hero = $hero;
    $this->league_tier = $league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->game_type = $game_type;
    $this->timeframe = $timeframe;
    $this->game_map = $game_map;
    $this->build_type = $build_type;
    $this->filter_type = "win_rate";
    $this->build_max_level = $build_max_level;
    $this->hero_level = $hero_level;
    $this->timeframe_type = $timeframe_type;
    //echo "build_max_level = " . $build_max_level;
  }

  public function getData(){
    $db = (new DBConnection())->getConnection();

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

    $most_played_builds = $this->getTopFiveBuilds($db, $versions);
      for($i = 0; $i < count($most_played_builds); $i++){
        $most_played_builds[$i]["wins"] = 0;
        $most_played_builds[$i]["losses"] = 0;

        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $versions, $most_played_builds[$i], $db, 10);
        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $versions, $most_played_builds[$i], $db, 13);
        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $versions, $most_played_builds[$i], $db, 16);

        if($this->build_max_level != "max_level_16"){
          $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $versions, $most_played_builds[$i], $db, 20);
        }


        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $versions, $most_played_builds[$i], $db, 10);
        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $versions, $most_played_builds[$i], $db, 13);
        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $versions, $most_played_builds[$i], $db, 16);
        if($this->build_max_level != "max_level_16"){
          $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $versions, $most_played_builds[$i], $db, 20);
        }
      }
      $returnData = array();
      for($i = 0; $i < count($most_played_builds); $i++){
        $data = array();



        $data["level_one"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_one"]];
        $data["level_four"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_four"]];
        $data["level_seven"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_seven"]];
        $data["level_ten"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_ten"]];
        $data["level_thirteen"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_thirteen"]];
        $data["level_sixteen"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_sixteen"]];
        if($this->build_max_level != "max_level_16"){
          $data["level_twenty"] = $_SESSION['talent_ids'][$most_played_builds[$i]["level_twenty"]];
        }


        $talentData = getTalentInfo($_SESSION['heroes'][$this->hero]["short_name"]);
        $data["talent_data"] = $talentData;
        if($this->build_max_level != "max_level_16"){
          $data = getTalentData($_SESSION['heroes'][$this->hero]["short_name"], $data, $data["level_one"], $data["level_four"], $data["level_seven"], $data["level_ten"], $data["level_thirteen"], $data["level_sixteen"], $data["level_twenty"]);
        }else{
          $data = getTalentData($_SESSION['heroes'][$this->hero]["short_name"], $data, $data["level_one"], $data["level_four"], $data["level_seven"], $data["level_ten"], $data["level_thirteen"], $data["level_sixteen"], 0);
        }

        $data["wins"] = $most_played_builds[$i]["wins"];
        $data["losses"] = $most_played_builds[$i]["losses"];

        $data["games_played"] = $data["wins"] + $data["losses"];

        $data["win_rate"] = ($data["wins"] / ($data["wins"] + $data["losses"])) * 100;

        //print_r(json_encode($most_played_builds,true));
        if($this->filter_type != "win_rate"){
          //$data[$this->filter_type] = ($most_played_builds[$i]["wins_" . $this->filter_type] + $most_played_builds[$i]["losses_" . $this->filter_type] ) / $data["games_played"];

        }

        $returnData[$i] = $data;
      }



      for($i = 0; $i < count($returnData); $i++){
        $newArray[$i] = $returnData[$i];
      }

      usort($newArray, [$this, 'cmp']);





    return $newArray;


  }

  private function getTopFiveBuilds($db, $versions){
    $newArray = array();

    $sql = "SELECT * FROM " . "(" . "SELECT" .
    " heroesprofile.global_hero_talents.game_type," .
    " heroesprofile.global_hero_talents.hero," .
    " talent_combinations.level_one," .
    " talent_combinations.level_four," .
    " talent_combinations.level_seven," .
    " talent_combinations.level_ten," .
    " talent_combinations.level_thirteen," .
    " talent_combinations.level_sixteen," .
    " talent_combinations.level_twenty," .
    " SUM(games_played) as games_played" .
    " FROM heroesprofile.global_hero_talents " .
    " inner join talent_combinations on talent_combinations.talent_combination_id = heroesprofile.global_hero_talents.talent_combination_id" .
    " where game_version in (";

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

    $sql .= " AND heroesprofile.global_hero_talents.hero = " . $_SESSION['heroes'][$this->hero]["id"];
    $sql .= " AND mirror = 0";


      $sql .= " AND not talent_combinations.level_twenty = 0";
      $sql .= " group by heroesprofile.global_hero_talents.game_type, heroesprofile.global_hero_talents.hero, talent_combinations.level_one, talent_combinations.level_four, talent_combinations.level_seven, talent_combinations.level_ten, talent_combinations.level_thirteen, talent_combinations.level_sixteen, talent_combinations.level_twenty";


      if($this->build_type == "Popular"){
        $sql .= " order by games_played DESC LIMIT 5";
      }else{
        $sql .= " order by games_played DESC LIMIT 100";
      }
      $sql .= ") as talent_build_query1";
      $most_played_builds = array();
      $counter = 0;
      $buildCounter = 0;

      //echo $sql;
      //echo "<br />\n";
      //echo "<br />\n";


      $result = $db->query($sql);
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            $data = array();
            $data["level_one"] = $row["level_one"];
            $data["level_four"] = $row["level_four"];
            $data["level_seven"] = $row["level_seven"];
            $data["level_ten"] = $row["level_ten"];
            $data["level_thirteen"] = $row["level_thirteen"];
            $data["level_sixteen"] = $row["level_sixteen"];
            if($this->build_max_level != "max_level_16"){
              $data["level_twenty"] = $row["level_twenty"];
            }

            if($this->build_type == "Popular"){
              $most_played_builds[$counter] = $data;
              $counter++;

            }else{
              if($counter != 0){
                $foundMatch = false;
                for($i = 0; $i < count($most_played_builds); $i++){

                  if($this->build_type == "HP"){
                    if($data["level_one"] == $most_played_builds[$i]["level_one"] && $data["level_four"] == $most_played_builds[$i]["level_four"] && $data["level_seven"] == $most_played_builds[$i]["level_seven"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "1"){
                    if($data["level_one"] == $most_played_builds[$i]["level_one"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "4"){
                    if($data["level_four"] == $most_played_builds[$i]["level_four"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "7"){
                    if($data["level_seven"] == $most_played_builds[$i]["level_seven"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "10"){
                    if($data["level_ten"] == $most_played_builds[$i]["level_ten"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "13"){
                    if($data["level_thirteen"] == $most_played_builds[$i]["level_thirteen"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "16"){
                    if($data["level_sixteen"] == $most_played_builds[$i]["level_sixteen"]){
                      $foundMatch = true;
                    }
                  }else if($this->build_type == "20"){
                    if($data["level_twenty"] == $most_played_builds[$i]["level_twenty"]){
                      $foundMatch = true;
                    }
                  }
                }

                if(!$foundMatch){
                    $most_played_builds[$counter] = $data;
                    $counter++;


                }

              }else{
                $most_played_builds[$counter] = $data;
                $counter++;
              }

              if($counter == 5){
                break;
              }
            }

          }
      }
      return $most_played_builds;

  }

  private function getWinLoss($value, $winLoss, $patchData, $most_played_builds, $db, $level){
    $sql = "SELECT * FROM " . "(" . "SELECT";
    if($this->filter_type != "win_rate"){
      $sql .= " SUM(" . $this->filter_type . ") as total_" . $this->filter_type . ",";
      $sql .= " SUM(games_played) as games_played";
    }else{
      $sql .= " SUM(games_played) as games_played";
    }

    $sql .= " FROM heroesprofile.global_hero_talents" .
    " inner join talent_combinations on talent_combinations.talent_combination_id = heroesprofile.global_hero_talents.talent_combination_id";

    $sql .= " WHERE game_version in (";

    for($i = 0; $i < count($patchData); $i++){
      if($i == 0){
        $sql .= "\"" . $patchData[$i] . "\"";

      }else{
        $sql .= "," . "\"" . $patchData[$i] . "\"";

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

    $sql .= " AND heroesprofile.global_hero_talents.hero = " . $_SESSION['heroes'][$this->hero]["id"];


    $sql .= " AND win_loss = " . $value;
    $sql .= " AND talent_combinations.level_one = " . $most_played_builds["level_one"] .
    " AND talent_combinations.level_four = " . $most_played_builds["level_four"] .
    " AND talent_combinations.level_seven = " . $most_played_builds["level_seven"] .
    " AND talent_combinations.level_ten = " . $most_played_builds["level_ten"];

    if($level == 13){
      $sql .= " AND talent_combinations.level_thirteen = " . $most_played_builds["level_thirteen"];
    }

    if($level == 16){
      $sql .= " AND talent_combinations.level_thirteen = " . $most_played_builds["level_thirteen"] .
      " AND talent_combinations.level_sixteen = " . $most_played_builds["level_sixteen"];
    }

    if($level == 20 && $this->build_max_level != "max_level_16"){
      $sql .= " AND talent_combinations.level_thirteen = " . $most_played_builds["level_thirteen"] .
      " AND talent_combinations.level_sixteen = " . $most_played_builds["level_sixteen"] .
      " AND talent_combinations.level_twenty = " . $most_played_builds["level_twenty"];
    }
    $sql .= ") as talent_build_query2";
    //echo $sql;
    //echo "<br />\n";
    //echo "<br />\n";

    $data = 0;

    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $data =  $row["games_played"];

        if($this->filter_type != "win_rate"){
          //$data[$this->filter_type] = $row["total_" . $this->filter_type];
        }

      }
    }
    return $data;
  }

  function cmp( $a, $b ) {
    if($a[$this->filter_type] ==  $b[$this->filter_type] ){ return 0 ; }
    return ($a[$this->filter_type] > $b[$this->filter_type]) ? -1 : 1;
  }
}

?>
