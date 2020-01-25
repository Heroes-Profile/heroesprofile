<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
  public function show(Request $request)
  {

$battletag = $request['battletag'];
$blizz_id = "1940";
$region = "1";
    $player_instance = \ProfileData::instance("Zemill#1940", 67280, 1, "", "");
    $profiledata = $player_instance->getPlayerProfileData();

    $mostplayed = array (

        // Every array will be converted
        // to an object
        array(
            "title" => 'Abathur',
            "imgurl"=> '/images/heroes/abathur.png',
            "value"=> 205
        ),
        array(
            "title" => 'Li-Ming',
            "imgurl"=> '/images/heroes/liming.png',
            "value"=> 200
        ),
        array(
            "title" => 'Ana',
            "imgurl"=> '/images/heroes/ana.png',
            "value"=> 165
        ),
    );
    $winrates = array(
      array(
        "title" => "Overall",
        "value" => 52.59
      ),
      array(
        "title" => "Ranged Assassin",
        "value" => 52.82
      ),
      array(
        "title" => "Bruiser",
        "value" => 56.2
      ),
      array(
        "title" => "Support",
        "value" => 56.2
      ),
      array(
        "title" => "Tank",
        "value" => 49.53
      ),
      
      array(
        "title" => "Healer",
        "value" => 52.34
      ),
      array(
        "title" => "Melee Assassin",
        "value" => 51.69
      )
    );


  /*  $player_instance = \ProfileData::instance('Zemill#1940', $request['blizz_id'], $request['region']);
    $data = $player_instance->getPlayerData();*/

    //print_r(json_encode(session(['player_data']), true));
    return view('Profile.profile',
    [
      'title'  => $battletag . "'s Profile",
      'paragraph' => $battletag . "'s Profile data.  Summary of heroes played, maps, MMR and more.",
      'blizz_id' => $blizz_id,
      'battletag' => $battletag,
      'region' => $region,
      'profiledata' => $profiledata,
      'mostplayed' => $mostplayed,
      'winrates' => $winrates
    ]);
  }
}
