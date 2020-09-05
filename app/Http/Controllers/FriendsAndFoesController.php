<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FriendsAndFoesController extends Controller
{
  public function show(){
    return view('Profile.FriendsAndFoes.home',
    [
      'title' => 'Friends and Foes', // Page title
      'paragraph' => 'Profile Page', // Summary paragraph
      'page' => 'profile',
      'inputUrl' => 'getFriendAndFoeData'
    ]);
  }

  public function getData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $friendsAndFoesData = new \FriendsAndFoes($blizz_id, $region, $game_type, $season);
    $return_data = $friendsAndFoesData->getFriendsAndFoesData();
    return $return_data;
  }
}
