<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class ProfileController extends Controller
{
  public function show(){

    return view('Profile.home',
    [
      'title' => 'Profile', // Page title
      'paragraph' => 'Profile Page', // Summary paragraph
      'page' => 'profile',
      'inputUrl' => 'getProfileData'
    ]);
  }


  public function getData(Request $request){
    switch ($request["page"]) {
    case "profile":
        return $this->getProfileData($request);
        break;
    default:
        return "Invalid";
        break;
      }
  }

  private function getProfileData($request){
    $blizz_id = $request["blizz_id"];
    $region = $request["region"];

    $cache = "Profile" . "-" . $blizz_id . "-" . $region;



    //$return_data = Cache::rememberForever($cache, function () use ($blizz_id, $region){
    $return_data = Cache::remember($cache, 1, function () use ($blizz_id, $region){
      $profile_data = new \ProfileData($blizz_id, $region);
      $return_data = $profile_data->getPlayerProfileData();
      return $return_data;
    });

    return $return_data;
  }
}
