<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;

class GlobalCompositionData
{
  private $game_versions_minor;
  private $game_type;
  private $region;
  private $game_map;
  private $hero_level;
  private $stat_type;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $mirror;
  private $hero;

  public function __construct($game_versions_minor, $game_type, $region, $game_map,
  $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $hero = "") {
    $this->game_versions_minor = $game_versions_minor;
    $this->game_type = $game_type;
    $this->region = $region;
    $this->game_map = $game_map;
    $this->hero_level = $hero_level;
    $this->stat_type = $stat_type;
    $this->player_league_tier = $player_league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->mirror = $mirror;
    $this->hero = $hero;
  }

  public function getDataForDrafter($teamPicked){
    /*
    echo "teamPicked = ";
    print_r($teamPicked);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */

    $role_array = array();
    for($i = 0; $i < count($teamPicked); $i++){
      $role = \App\Models\Hero::select('new_role')->where('id', $teamPicked[$i])->first();
      $role_array[$i] = \App\Models\MMRTypeID::where('name', $role['new_role'])->value('mmr_type_id');
    }

    sort($role_array);

    /*8
    echo "role_array = ";
    print_r($role_array);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */

    $roleString = "";
    for($i = 0; $i < count($role_array); $i++){
      $roleString .= ("%" . $role_array[$i]);
    }
    $roleString .= "%";
    /*
    echo "roleString = ";
    print_r($roleString);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */

    $valid_compositions = DB::connection('mysql')->select('SELECT composition_id FROM heroesprofile.compositions where CONCAT(role_one, role_two, role_three, role_four, role_five) like ' . "\"" . $roleString . "\"");
    $valid_compositions = json_decode(json_encode($valid_compositions),true);

    $key_values = array();
    $counter = 0;
    foreach ($valid_compositions as $key => $value)
    {
      $key_values[$counter] = $value['composition_id'];
      $counter++;
    }

    /*
    print_r(json_encode($key_values, true));
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */

    //print_r($teamPicked);
    $composition_id = \App\Models\GlobalCompositions::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
    $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror, $this->hero)
    ->join('compositions', 'compositions.composition_id', 'global_compositions.composition_id')
    ->selectRaw('compositions.composition_id, SUM(games_played) as games_played')
    ->whereIn('compositions.composition_id', $key_values)
    ->groupBy('compositions.composition_id')
    ->orderBy('games_played', 'DESC')
    /*
    print_r($composition_id->toSql());
    echo "<br>";
    print_r($composition_id->getBindings());
    echo "<br>";
    */
    ->limit(1)
    ->value('composition_id');




    /*
    echo "composition_id = ";
    print_r($composition_id);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */


    $composition = \App\Models\Composition::Filters($composition_id)->get()->toArray();

    $bruiser_count = 0;
    $support_count = 0;
    $ranged_assassin_count = 0;
    $melee_assassin_count = 0;
    $healer_count = 0;
    $tank_count = 0;

    /*
    echo "composition = ";
    print_r($composition);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */
    for($i = 0; $i < count($composition); $i++){
      foreach($composition[0] as $role => $value){
        if($value == 100000){
          $support_count++;
        }else if($value == 100001){
          $melee_assassin_count++;
        }else if($value == 100002){
          $tank_count++;
        }else if($value == 100003){
          $bruiser_count++;
        }else if($value == 100004){
          $healer_count++;
        }else if($value == 100005){
          $ranged_assassin_count++;
        }
      }

    }


    $valid_roles = array();
    $valid_counter = 0;

    /*
    echo "Team Pick = ";
    print_r($teamPicked);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    */

    /*
    echo "bruiser_count = ";
    print_r($bruiser_count);
    echo "<br>";



    echo "support_count = ";
    print_r($support_count);
    echo "<br>";


    echo "ranged_assassin_count = ";
    print_r($ranged_assassin_count);
    echo "<br>";


    echo "melee_assassin_count = ";
    print_r($melee_assassin_count);
    echo "<br>";

    echo "healer_count = ";
    print_r($healer_count);
    echo "<br>";

    echo "tank_count = ";
    print_r($tank_count);
    echo "<br>";
    */

    for($i = 0; $i < count($teamPicked); $i++){
      $hero_data = \App\Models\Hero::select('id', "mmr_type_id")
      ->join('mmr_type_ids', 'mmr_type_ids.name', '=', 'heroes.new_role')
      ->where('id', $teamPicked[$i])->first();



      $hero_role = $hero_data->mmr_type_id;




      if($hero_role == 100000){
        $support_count--;
      }else if($hero_role == 100001){
        $melee_assassin_count--;
      }else if($hero_role == 100002){
        $tank_count--;
      }else if($hero_role == 100003){
        $bruiser_count--;
      }else if($hero_role == 100004){
        $healer_count--;
      }else if($hero_role == 100005){
        $ranged_assassin_count--;
      }
    }

    if($support_count != 0){
      $valid_roles[$valid_counter] = "'Support'";
      $valid_counter++;
    }

    if($melee_assassin_count != 0){
      $valid_roles[$valid_counter] = "'Melee Assassin'";
      $valid_counter++;
    }

    if($tank_count != 0){
      $valid_roles[$valid_counter] = "'Tank'";
      $valid_counter++;
    }

    if($bruiser_count != 0){
      $valid_roles[$valid_counter] = "'Bruiser'";
      $valid_counter++;
    }

    if($healer_count != 0){
      $valid_roles[$valid_counter] = "'Healer'";
      $valid_counter++;
    }

    if($ranged_assassin_count != 0){
      $valid_roles[$valid_counter] = "'Ranged Assassin'";
      $valid_counter++;
    }

    $heroes = \App\Models\GlobalCompositions::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
    $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
    ->selectRaw('hero, if(new_role in (' . implode(",", $valid_roles) . '), SUM(games_played), 0) as games_played')
    ->where('composition_id', $composition_id)
    ->groupBy('hero')
    ->orderBy('games_played', 'DESC')
    ->get();

    /*
    print_r($heroes->toSql());
    echo "<br>";
    print_r($heroes->getBindings());
    echo "<br>";
    */


    return $heroes;
  }
}
