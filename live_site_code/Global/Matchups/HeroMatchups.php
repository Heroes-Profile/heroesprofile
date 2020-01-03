<?php
Class HeroMatchups{

  private $league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_type;
  private $map;
  private $timeframe;
  private $hero;
  private $role;
  private $hero_level;
  private $timeframe_type;

  function __construct($league_tier, $hero_league_tier, $role_league_tier, $game_type, $map, $timeframe_type, $timeframe, $hero, $role, $hero_level){
    $this->league_tier = $league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->game_type = $game_type;
    $this->timeframe = $timeframe;
    $this->map = $map;
    $this->hero = $hero;
    $this->role = $role;
    $this->hero_level = $hero_level;
    $this->timeframe_type = $timeframe_type;
  }

  public function getData(){
    $returnData = array();
    $returnData = $this->getAllyEnemyData("ally", $returnData);
    $returnData = $this->getAllyEnemyData("enemy", $returnData);
    return $returnData;
  }

  private function getAllyEnemyData($type, $returnData){
    $db = (new DBConnection())->getConnection();
    $sql = "SELECT id FROM heroesprofile.heroes where name = " . "\"" . $this->hero . "\"";
    $hero_id = 0;
    //echo $sql;
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $hero_id = $row["id"];
        }
    }

    $versions = array();

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
    $sql = "SELECT id FROM heroesprofile.heroes order by name ASC";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $returnData[$row["id"]][$type][0] = 0;
          $returnData[$row["id"]][$type][1] = 0;
        }
    }


    $sql = "SELECT " .
    $type . ", " .
    " win_loss," .
    " SUM(games_played) as games_played " .
    " FROM global_hero_matchups_" . $type;
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

    if(!in_array("", $this->map)){

      $gameMapString = "";

      for($k = 0; $k < count($this->map); $k++){
        if($k == 0){
          $gameMapString .= $_SESSION['maps_ids'][$this->map[$k]];

        }else{
          $gameMapString .= "," . $_SESSION['maps_ids'][$this->map[$k]];

        }
      }
      $sql .= " AND game_map IN (" . $gameMapString . ")";

    }
    if(!in_array("", $this->hero_level)){

      if(count($this->hero_level) == 5){
        $heroLevelString = "0, ";

      }else{
        $heroLevelString = "";

      }

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


    $sql .= " AND hero = " . $hero_id;
    $sql .= " AND mirror = 0";




      $sql .= " GROUP by " . $type;

      $sql .= ", win_loss";

      $sql .= " ORDER BY " . $type . " ASC, win_loss ASC";

      //echo $sql;
      //echo "<br />\n";
      //echo "<br />\n";

      $result = $db->query($sql);
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            $returnData[$row[$type]][$type][$row["win_loss"]] = $row["games_played"];
          }
      }
    return $returnData;

  }
  function cmp( $a, $b ) {
    if(  $a[$this->filter_type] ==  $b[$this->filter_type] ){ return 0 ; }
    return ($a[$this->filter_type] > $b[$this->filter_type]) ? -1 : 1;
  }
}

?>
