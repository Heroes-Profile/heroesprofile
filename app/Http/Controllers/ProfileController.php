<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class ProfileController extends Controller
{
  //Profile Page
  public function profile(){
    return view('Profile.home',
    [
      'title' => 'Profile', // Page title
      'paragraph' => 'Profile Page', // Summary paragraph
      'page' => 'profile',
      'inputUrl' => 'getProfileData'
    ]);
  }

  public function getProfileData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $profile_data = new \ProfileData($blizz_id, $region, $game_type, $season);
    $return_data = $profile_data->getPlayerProfileData();
    return $return_data;
  }

  //Profile Friends and Foe Page
  public function friendsAndFoes(){
    return view('Profile.FriendsAndFoes.home',
    [
      'title' => 'Friends and Foes', // Page title
      'paragraph' => 'Profile Page', // Summary paragraph
      'page' => 'profile',
      'inputUrl' => '/profile/getFriendAndFoeData'
    ]);
  }

  public function getFriendsAndFoesData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $friendsAndFoesData = new \FriendsAndFoes($blizz_id, $region, $game_type, $season);
    $return_data = $friendsAndFoesData->getFriendsAndFoesData();
    return $return_data;
  }

  //Profile Hero All Page
  public function heroAll(){
    return view('Profile.Hero.All.home',
    [
      'title' => 'Heroes All', // Page title
      'paragraph' => 'Heroes All', // Summary paragraph
      'page' => 'heroes',
      'inputUrl' => '/profile/getHeroAllData'
    ]);
  }

  public function getHeroAllData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $hero_data = new \ProfileHero($blizz_id, $region, $game_type, $season);
    $return_data = $hero_data->getHeroAllData();
    return $return_data;
  }

  //Profile Map All Page
  public function mapAll(){
    return view('Profile.Map.All.home',
    [
      'title' => 'Maps All', // Page title
      'paragraph' => 'Maps All', // Summary paragraph
      'page' => 'Map',
      'inputUrl' => '/profile/getMapAllData'
    ]);
  }

  public function getMapAllData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $map_data = new \ProfileMap($blizz_id, $region, $game_type, $season);
    $return_data = $map_data->getMapAllData();

    return $return_data;
  }

  //Profile Match History Page
  public function matchHistory(){
    return view('Profile.Match.History.home',
    [
      'title' => 'Match History', // Page title
      'paragraph' => 'Match History', // Summary paragraph
      'page' => 'Match History',
      'inputUrl' => '/profile/getMatchHistoryData'
    ]);
  }

  public function getMatchHistoryData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $match_data = new \ProfileMatchHistory($blizz_id, $region, $game_type, $season);
    $return_data = $match_data->getMatchData();
    return $return_data;
  }

  //Profile Matchups Page
  public function matchups(){
    return view('Profile.Matchups.home',
    [
      'title' => 'Matchups', // Page title
      'paragraph' => 'Matchups', // Summary paragraph
      'page' => 'Matchups',
      'inputUrl' => '/profile/getMatchupsData'
    ]);
  }

  public function getMatchupsData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $matchup_data = new \ProfileMatchups($blizz_id, $region, $game_type, $season);
    $return_data = $matchup_data->getMatchupData();
    return $return_data;
  }

  //Profile MMR Page
  public function mmr(){
    return view('Profile.MMR.home',
    [
      'title' => 'MMR', // Page title
      'paragraph' => 'MMR', // Summary paragraph
      'page' => 'MMR',
      'inputUrl' => '/profile/getMMRData'
    ]);
  }

  public function getMMRData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $return_data = array();
    return $return_data;
  }

  //Profile Role All Page
  public function roleAll(){
    return view('Profile.Role.All.home',
    [
      'title' => 'MMR', // Page title
      'paragraph' => 'MMR', // Summary paragraph
      'page' => 'MMR',
      'inputUrl' => '/profile/getRoleAllData'
    ]);
  }

  public function getRoleAllData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $return_data = array();
    return $return_data;
  }

  //Profile Talents Page
  public function talents(){
    return view('Profile.Talents.home',
    [
      'title' => 'MMR', // Page title
      'paragraph' => 'MMR', // Summary paragraph
      'page' => 'MMR',
      'inputUrl' => '/profile/getTalentsData'
    ]);
  }

  public function getTalentsData(Request $request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];
    $game_type = $request["game_type"];
    $season = $request["season"];

    $return_data = array();
    return $return_data;
  }
}
